<?php

session_start();
require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

require_once "../includes/activity_logger.php";
require_once "../config/firebase_messaging.php";

header("Content-Type: application/json");


// ==========================
// AUTHENTICATION
// ==========================

if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);

    exit();
}

$role = $_SESSION['role'] ?? '';

if (in_array($role, [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff'
], true)) {

    header("Location: ../patient/view_consultation.php");
    exit();

}

if ($role === 'Administrator') {

    header("Location: ../admin/accounts.php");
    exit();

}

// ==========================
// REQUEST METHOD
// ==========================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit();
}


// ==========================
// GET FORM DATA
// ==========================

$date = $_POST['consultation_date'] ?? null;
$time = $_POST['consultation_time'] ?? null;
$patient_type = $_POST['patient_type'] ?? null;
$patient_id_number = trim($_POST['patient_id_number'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$middle_name = trim($_POST['middle_name'] ?? '');

$full_name = $last_name . ', ' . $first_name;

if ($middle_name !== '') {
    $full_name .= ' ' . $middle_name;
}
$age = $_POST['age'] ?? null;
$sex = $_POST['sex'] ?? null;
$contact_number = $_POST['contact_number'] ?? null;
$department = $_POST['department'] ?? null;
$course = $_POST['course'] ?? null;
$year_level = $_POST['year_level'] ?? null;
$position = $_POST['position'] ?? null;
$complaint = $_POST['complaint'] ?? null;
$treatment_given = $_POST['treatment'] ?? null;
$remarks = $_POST['remarks'] ?? null;
$appointment_id = (int) ($_POST['appointment_id'] ?? 0);
$appointment_mode = $_POST['appointment_mode'] ?? 'Walk-in';
$appointment_reason = null;

// ==========================
// MEDICINES
// ==========================

$medicines = $_POST['medicines'] ?? [];
$quantities = $_POST['medicine_quantities'] ?? [];

// ==========================
// START TRANSACTION
// ==========================

$conn->begin_transaction();

try {

// ==========================
// APPOINTMENT VALIDATION
// ==========================

if (!in_array($appointment_mode, ['Appointment', 'Walk-in'], true)) {
    throw new Exception("Invalid appointment mode.");
}

if (
    ($appointment_mode === 'Appointment' && $appointment_id <= 0) ||
    ($appointment_mode === 'Walk-in' && $appointment_id > 0)
) {
    throw new Exception("Invalid appointment information.");
}

// ==========================
// BASIC VALIDATION
// ==========================

if (
    empty($date) ||
    empty($time) ||
    empty($patient_type) ||
    $patient_id_number === '' ||
    $last_name === '' ||
    $first_name === ''
) {
    throw new Exception("Please complete the required fields.");
}

// ==========================
// VERIFY PATIENT RECORD
// ==========================

$patient = null;

if ($patient_type === 'student') {

    $stmt = $conn->prepare("
        SELECT
            student_id_number,
            first_name,
            middle_name,
            last_name,
            date_of_birth,
            sex,
            contact_number,
            department,
            course,
            year_level
        FROM students
        WHERE student_id_number = ?
          AND is_archived = 0
        LIMIT 1
    ");

} elseif (in_array($patient_type, ['tstaff', 'nstaff'], true)) {

    $stmt = $conn->prepare("
        SELECT
            staff_id_number,
            first_name,
            middle_name,
            last_name,
            date_of_birth,
            sex,
            contact_number,
            department,
            position
        FROM staffs
        WHERE staff_id_number = ?
          AND is_archived = 0
        LIMIT 1
    ");

} else {
    throw new Exception("Invalid patient type.");
}

if (!$stmt) {
    throw new Exception("Failed to prepare patient verification query.");
}

$stmt->bind_param("s", $patient_id_number);
$stmt->execute();

$result = $stmt->get_result();
$patient = $result->fetch_assoc();

$stmt->close();

if (!$patient) {
    throw new Exception(
        "No active patient record found. Please retrieve the patient again."
    );
}

// Verify staff category
if ($patient_type !== 'student') {

    $actualPosition = strtolower(trim($patient['position']));

    if (
        ($patient_type === 'tstaff' && $actualPosition !== 'teaching staff') ||
        ($patient_type === 'nstaff' && $actualPosition !== 'non-teaching staff')
    ) {
        throw new Exception("Patient type does not match the staff record.");
    }
}

// Use verified database information
$last_name = $patient['last_name'];
$first_name = $patient['first_name'];
$middle_name = $patient['middle_name'] ?? '';

$full_name = $last_name . ', ' . $first_name;

if ($middle_name !== '') {
    $full_name .= ' ' . $middle_name;
}

$birthDate = new DateTime($patient['date_of_birth']);
$age = $birthDate->diff(new DateTime())->y;

$sex = $patient['sex'];
$contact_number = $patient['contact_number'];
$department = $patient['department'];

if ($patient_type === 'student') {

    $course = $patient['course'];

    $year = (int) $patient['year_level'];

    $suffix = match ($year) {
        1 => 'st',
        2 => 'nd',
        3 => 'rd',
        default => 'th'
    };

    $year_level = $year . $suffix . ' Year';
    $position = null;

} else {

    $position = $patient['position'];
    $course = null;
    $year_level = null;
}

    // VERIFY APPOINTMENT BEFORE SAVING CONSULTATION
    if ($appointment_mode === 'Appointment') {

        $appointmentStmt = $conn->prepare("
            SELECT
                a.id,
                a.user_id,
                a.purpose,
                a.status,
                u.student_id,
                u.staff_id,
                s.student_id_number,
                st.staff_id_number
            FROM appointments a
            INNER JOIN users u ON a.user_id = u.id
            LEFT JOIN students s ON u.student_id = s.id
            LEFT JOIN staffs st ON u.staff_id = st.id
            WHERE a.id = ?
            FOR UPDATE
        ");

        if (!$appointmentStmt) {
            throw new Exception("Unable to verify appointment.");
        }

        $appointmentStmt->bind_param("i", $appointment_id);
        $appointmentStmt->execute();

        $appointmentResult = $appointmentStmt->get_result();
        $appointment = $appointmentResult->fetch_assoc();

        $appointmentStmt->close();

        if (!$appointment) {
            throw new Exception("Appointment not found.");
        }

        if ($appointment['status'] !== 'Approved') {
            throw new Exception("This appointment is no longer approved.");
        }

        $appointmentPatientId = $appointment['student_id_number']
            ?: $appointment['staff_id_number'];

        if (
            !$appointmentPatientId ||
            $appointmentPatientId !== $patient_id_number
        ) {
            throw new Exception("The selected patient does not match this appointment.");
        }

        $appointment_reason = $appointment['purpose'];

        $patientUserId = (int) $appointment['user_id'];

        // PREVENT DUPLICATE APPOINTMENT CONSULTATIONS
        $duplicateStmt = $conn->prepare("
            SELECT id
            FROM consultations
            WHERE appointment_id = ?
            LIMIT 1
        ");

        if (!$duplicateStmt) {
            throw new Exception("Unable to check existing consultation.");
        }

        $duplicateStmt->bind_param("i", $appointment_id);
        $duplicateStmt->execute();

        $duplicateResult = $duplicateStmt->get_result();
        $existingConsultation = $duplicateResult->fetch_assoc();

        $duplicateStmt->close();

        if ($existingConsultation) {
            throw new Exception("A consultation has already been recorded for this appointment.");
        }
    }
   
    // ==========================
    // INSERT CONSULTATION
    // ==========================

    $sql = "
        INSERT INTO consultations (
        appointment_id,
        appointment_mode,
        appointment_reason,
        date,
        time,
        patient_type,
        full_name,
        age,
        sex,
        contact_number,
        department,
        course,
        year_level,
        position,
        complaint,
        treatment_given,
        remarks
    )
        VALUES (
        NULLIF(?, 0),
        ?,
        ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            "Failed to prepare consultation query: " . $conn->error
        );
    }


    $stmt->bind_param(
        "issssssisssssssss",
        $appointment_id,
        $appointment_mode,
        $appointment_reason,
        $date,
        $time,
        $patient_type,
        $full_name,
        $age,
        $sex,
        $contact_number,
        $department,
        $course,
        $year_level,
        $position,
        $complaint,
        $treatment_given,
        $remarks
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Failed to save consultation: " . $stmt->error
        );
    }


    // Get newly created consultation ID
    $consultation_id = $stmt->insert_id;

    $stmt->close();


    // ==========================
    // PROCESS MEDICINES
    // ==========================

    $dispensedMedicines = [];

    for ($i = 0; $i < count($medicines); $i++) {

        $medicine_id = (int) $medicines[$i];

        $quantity = isset($quantities[$i])
            ? (int) $quantities[$i]
            : 0;


        
        // Skip rows where no batch was selected
        if ($medicine_id <= 0) {
            continue;
        }

        // A selected batch must have a valid quantity
        if ($quantity <= 0) {
            throw new Exception(
                "Please enter a valid quantity for each selected medicine batch."
            );
        }



        
        // ==========================
        // CHECK INVENTORY ELIGIBILITY
        // ==========================

        $inventorySql = "
            SELECT id, medicine_name, quantity
            FROM medicines
            WHERE id = ?
              AND status = 'Active'
              AND expiry_date >= CURDATE()
              AND is_quarantined = 0
            FOR UPDATE
        ";

        $inventoryStmt = $conn->prepare($inventorySql);

        if (!$inventoryStmt) {
            throw new Exception(
                "Failed to check medicine inventory."
            );
        }

        $inventoryStmt->bind_param(
            "i",
            $medicine_id
        );

        if (!$inventoryStmt->execute()) {
            $inventoryStmt->close();

            throw new Exception(
                "Failed to verify the selected medicine batch."
            );
        }

        $inventoryResult = $inventoryStmt->get_result();

        if ($inventoryResult->num_rows === 0) {
            $inventoryStmt->close();

            throw new Exception(
                "The selected medicine batch is unavailable, expired, inactive, or quarantined. Please select another batch."
            );
        }

        $inventory = $inventoryResult->fetch_assoc();

        $currentQuantity = (int) $inventory['quantity'];

        $dispensedMedicines[] =
        $inventory['medicine_name'] . " (Quantity: {$quantity})";

            $inventoryStmt->close();



        // ==========================
        // CHECK STOCK
        // ==========================

        if ($quantity > $currentQuantity) {

            throw new Exception(
                "Not enough stock available for the selected medicine."
            );
        }


        // ==========================
        // INSERT JUNCTION RECORD
        // ==========================

        $medicineInsertSql = "
            INSERT INTO consultation_medicine (
                consultation_id,
                medicine_id,
                quantity_used
            )
            VALUES (?, ?, ?)
        ";

        $medicineInsertStmt =
            $conn->prepare($medicineInsertSql);

        if (!$medicineInsertStmt) {

            throw new Exception(
                "Failed to record dispensed medicine."
            );
        }


        $medicineInsertStmt->bind_param(
            "iii",
            $consultation_id,
            $medicine_id,
            $quantity
        );


        if (!$medicineInsertStmt->execute()) {

            $medicineInsertStmt->close();

            throw new Exception(
                "Failed to record dispensed medicine: "
                . $medicineInsertStmt->error
            );
        }

        $medicineInsertStmt->close();


        // ==========================
        // DEDUCT INVENTORY
        // ==========================

        $updateSql = "
            UPDATE medicines
            SET quantity = quantity - ?
            WHERE id = ?
              AND quantity >= ?
        ";

        $updateStmt = $conn->prepare($updateSql);

        if (!$updateStmt) {

            throw new Exception(
                "Failed to prepare inventory update."
            );
        }


        $updateStmt->bind_param(
            "iii",
            $quantity,
            $medicine_id,
            $quantity
        );


        if (!$updateStmt->execute()) {

            $updateStmt->close();

            throw new Exception(
                "Failed to update medicine inventory."
            );
        }


        // Make sure something was actually deducted
        if ($updateStmt->affected_rows === 0) {

            $updateStmt->close();

            throw new Exception(
                "Medicine stock could not be deducted."
            );
        }

        $updateStmt->close();
    }

    // MARK APPOINTMENT AS COMPLETED
    if ($appointment_mode === 'Appointment') {

        $completeStmt = $conn->prepare("
            UPDATE appointments
            SET status = 'Completed'
            WHERE id = ?
            AND status = 'Approved'
        ");

        if (!$completeStmt) {
            throw new Exception("Unable to update appointment status.");
        }

        $completeStmt->bind_param("i", $appointment_id);
        $completeStmt->execute();

        if ($completeStmt->affected_rows !== 1) {
            $completeStmt->close();
            throw new Exception("Appointment could not be completed. Please try again.");
        }

        $completeStmt->close();
    }

    // ==========================
    // COMMIT
    // ==========================

    $conn->commit();

    // PATIENT NOTIFICATION
    if ($appointment_mode === 'Appointment') {

        try {
            $title = 'Appointment Completed';
            $message = 'Your appointment has been marked as completed.';

            $notificationStmt = $conn->prepare("
                INSERT INTO notifications (
                    user_id,
                    appointment_id,
                    title,
                    message
                )
                VALUES (?, ?, ?, ?)
            ");

            if (!$notificationStmt) {
                throw new Exception("Unable to prepare patient notification.");
            }

            $notificationStmt->bind_param(
                "iiss",
                $patientUserId,
                $appointment_id,
                $title,
                $message
            );

            if (!$notificationStmt->execute()) {
                throw new Exception("Unable to save patient notification.");
            }

            $notificationStmt->close();

        } catch (Throwable $notificationError) {
            error_log(
                "Patient notification failed: " .
                $notificationError->getMessage()
            );
        }
    }

    // FIREBASE PUSH NOTIFICATION
    if ($appointment_mode === 'Appointment') {

        sendFirebasePushToUser(
            $conn,
            $patientUserId,
            'Appointment Completed',
            'Your consultation has been completed. You can now view your consultation details.'
        );
    }

    // PATIENT ACTIVITY LOG
    if ($appointment_mode === 'Appointment') {

        try {
            logActivity(
                $conn,
                $patientUserId,
                'Appointment',
                'Completed',
                'Your appointment has been completed and a consultation record has been created.'
            );

        } catch (Throwable $activityError) {
            error_log(
                "Patient activity logging failed: " .
                $activityError->getMessage()
            );
        }
    }

    // ==========================
    // CLINIC ACTIVITY LOG
    // ==========================

    $medicineDetails = !empty($dispensedMedicines)
        ? implode(', ', $dispensedMedicines)
        : 'No medicines dispensed';

    try {
        logClinicActivity(
            $conn,
            'Created',
            'Consultations',
            "Recorded consultation for {$full_name}. Medicines dispensed: {$medicineDetails}."
        );
    } catch (Throwable $logError) {
        error_log("Clinic activity logging failed: " . $logError->getMessage());
    }

    echo json_encode([
        "success" => true,
        "message" => "Consultation saved successfully.",
        "consultation_id" => $consultation_id
    ]);


} catch (Exception $e) {


    // ==========================
    // ROLLBACK
    // ==========================

    $conn->rollback();


    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}


$conn->close();
?>