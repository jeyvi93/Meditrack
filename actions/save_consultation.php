<?php

session_start();
require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

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


// ==========================
// MEDICINES
// ==========================

$medicines = $_POST['medicines'] ?? [];
$quantities = $_POST['medicine_quantities'] ?? [];


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

    echo json_encode([
        "success" => false,
        "message" => "Please complete the required fields."
    ]);

    exit();
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

// ==========================
// START TRANSACTION
// ==========================

$conn->begin_transaction();

try {


    // ==========================
    // INSERT CONSULTATION
    // ==========================

    $sql = "
        INSERT INTO consultations (
            date,
            time,
            patient_type,
            patient_id_number,
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
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            "Failed to prepare consultation query: " . $conn->error
        );
    }


    $stmt->bind_param(
        "sssssisssssssss",
        $date,
        $time,
        $patient_type,
        $patient_id_number,
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

    // ==========================
    // CLINIC ACTIVITY LOG
    // ==========================

    $medicineDetails = !empty($dispensedMedicines)
        ? implode(', ', $dispensedMedicines)
        : 'No medicines dispensed';

    logClinicActivity(
        $conn,
        'Created',
        'Consultations',
        "Recorded consultation for {$full_name}. Medicines dispensed: {$medicineDetails}."
    );

    // ==========================
    // COMMIT
    // ==========================

    $conn->commit();


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