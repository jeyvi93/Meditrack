<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: ../clinic/staffs.php");
    exit();
}

require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

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


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CSRF TOKEN VALIDATION
    $submittedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        empty($submittedToken) ||
        empty($sessionToken) ||
        !hash_equals($sessionToken, $submittedToken)
    ) {
        $_SESSION['error'] = "Invalid or expired form submission. Please try again.";
        header("Location: ../clinic/staffs.php");
        exit();
    }

    // ==========================
    // STAFF ID
    // ==========================

    $staffId = (int)($_POST['id'] ?? 0);

    if ($staffId <= 0) {
        $_SESSION['error'] = "Invalid staff record.";
        header("Location: ../clinic/staffs.php");
        exit();
    }

    // ==========================
    // HEALTH INFORMATION
    // ==========================

    $blood_type = trim($_POST['blood_type'] ?? '') !== ''
    ? trim($_POST['blood_type'])
    : null;

    // BLOOD TYPE VALIDATION
    $allowedBloodTypes = [
        'A+', 'A-', 'B+', 'B-',
        'O+', 'O-', 'AB+', 'AB-'
    ];

    if (
        $blood_type !== null &&
        !in_array($blood_type, $allowedBloodTypes, true)
    ) {
        $_SESSION['error'] = "Please select a valid blood type.";
        header("Location: ../clinic/edit_staff.php?id=" . $staffId);
        exit();
    }

    $height = trim($_POST['height'] ?? '') !== ''
        ? trim($_POST['height'])
        : null;

    $weight = trim($_POST['weight'] ?? '') !== ''
        ? trim($_POST['weight'])
        : null;
    
    // ==========================
    // HEIGHT / WEIGHT VALIDATION
    // ==========================

    if (
        ($height !== null && (!is_numeric($height) || $height <= 0 || $height > 999.99)) ||
        ($weight !== null && (!is_numeric($weight) || $weight <= 0 || $weight > 999.99))
    ) {
        $_SESSION['error'] = "Height and weight must be valid positive numbers.";
        header("Location: ../clinic/edit_staff.php?id=" . $staffId);
        exit();
    }

    $allergies = trim($_POST['allergies'] ?? '') ?: null;
    $medical_history = trim($_POST['medical_history'] ?? '') ?: null;
    $current_medications = trim($_POST['current_medications'] ?? '') ?: null;
    $vaccines = trim($_POST['vaccines'] ?? '') ?: null;

    // ==========================
    // INSURANCE INFORMATION
    // ==========================

    $philhealth_number = trim($_POST['philhealth_number'] ?? '');
    $insurance_number = trim($_POST['insurance_number'] ?? '');
    $insurance_company = trim($_POST['insurance_company'] ?? '');


    // ==========================
    // EMERGENCY CONTACT
    // ==========================

    $emergency_name = trim($_POST['emergency_name'] ?? '');
    $emergency_relationship = trim($_POST['emergency_relationship'] ?? '');
    $emergency_contact = trim($_POST['emergency_contact'] ?? '');

    // ==========================
    // REQUIRED EMERGENCY CONTACT
    // ==========================

    if (
        $emergency_name === '' ||
        $emergency_relationship === '' ||
        $emergency_contact === ''
    ) {
        $_SESSION['error'] = "All emergency contact fields are required.";
        header("Location: ../clinic/edit_staff.php?id=" . $staffId);
        exit();
    }


    // Emergency name validation if provided
    if (
        $emergency_name !== '' &&
        !preg_match("/^[A-Za-zÀ-ÿ\s.,'-]+$/", $emergency_name)
    ) {
        $_SESSION['error'] = "Emergency contact name should contain letters, spaces, and common name punctuation only.";
        header("Location: ../clinic/edit_staff.php?id=" . $staffId);
        exit();
    }


    // Emergency relationship validation if provided
    if (
        $emergency_relationship !== '' &&
        !preg_match("/^[A-Za-zÀ-ÿ\s-]+$/", $emergency_relationship)
    ) {
        $_SESSION['error'] = "Emergency relationship should contain letters and spaces only.";
        header("Location: ../clinic/edit_staff.php?id=" . $staffId);
        exit();
    }


    // Emergency contact validation if provided
    if (
        $emergency_contact !== '' &&
        !preg_match('/^09[0-9]{9}$/', $emergency_contact)
    ) {
        $_SESSION['error'] = "Emergency contact must contain exactly 11 digits and start with 09.";
        header("Location: ../clinic/edit_staff.php?id=" . $staffId);
        exit();
    }

    // ==========================
    // GET ORIGINAL STAFF INFORMATION
    // ==========================

    $oldSql = "
        SELECT *
        FROM staffs
        WHERE id = ? AND is_archived = 0
        LIMIT 1
    ";

    $oldStmt = mysqli_prepare($conn, $oldSql);

    if (!$oldStmt) {
        $_SESSION['error'] = "Failed to retrieve original staff information.";
        mysqli_close($conn);
        header("Location: ../clinic/staffs.php");
        exit();
    }

    mysqli_stmt_bind_param($oldStmt, "i", $staffId);
    mysqli_stmt_execute($oldStmt);

    $oldResult = mysqli_stmt_get_result($oldStmt);
    $oldStaff = mysqli_fetch_assoc($oldResult);

    mysqli_stmt_close($oldStmt);

    if (!$oldStaff) {
        $_SESSION['error'] = "Staff record not found.";
        mysqli_close($conn);
        header("Location: ../clinic/staffs.php");
        exit();
    }

    // ==========================
    // COMPARE OLD AND NEW VALUES
    // ==========================

    $fieldLabels = [
        'blood_type'             => 'blood type',
        'height'                 => 'height',
        'weight'                 => 'weight',
        'allergies'              => 'allergies',
        'medical_history'        => 'medical history',
        'current_medications'    => 'current medications',
        'vaccines'               => 'vaccines',
        'philhealth_number'      => 'PhilHealth number',
        'insurance_number'       => 'insurance number',
        'insurance_company'      => 'insurance company',
        'emergency_name'         => 'emergency contact name',
        'emergency_relationship' => 'emergency relationship',
        'emergency_contact'      => 'emergency contact number'
    ];

    $newValues = [
        'blood_type'             => $blood_type,
        'height'                 => $height,
        'weight'                 => $weight,
        'allergies'              => $allergies,
        'medical_history'        => $medical_history,
        'current_medications'    => $current_medications,
        'vaccines'               => $vaccines,
        'philhealth_number'      => $philhealth_number,
        'insurance_number'       => $insurance_number,
        'insurance_company'      => $insurance_company,
        'emergency_name'         => $emergency_name,
        'emergency_relationship' => $emergency_relationship,
        'emergency_contact'      => $emergency_contact
    ];

    $changes = [];

    foreach ($fieldLabels as $field => $label) {

        $oldValue = $oldStaff[$field] ?? '';
        $newValue = $newValues[$field] ?? '';

        if (in_array($field, ['height', 'weight'], true)) {

            $oldValue = ($oldValue === '' || $oldValue === null)
                ? ''
                : rtrim(rtrim(number_format((float)$oldValue, 2, '.', ''), '0'), '.');

            $newValue = ($newValue === '' || $newValue === null)
                ? ''
                : rtrim(rtrim(number_format((float)$newValue, 2, '.', ''), '0'), '.');

        } else {

            $oldValue = trim((string)$oldValue);
            $newValue = trim((string)$newValue);

        }

        if ($oldValue !== $newValue) {
            $changes[] = "{$label} {$oldValue} --> {$newValue}";
        }
    }

    // ==========================
    // UPDATE STAFF RECORD
    // ==========================

    $sql = "UPDATE staffs SET
        blood_type = ?,
        height = ?,
        weight = ?,
        allergies = ?,
        medical_history = ?,
        current_medications = ?,
        vaccines = ?,
        philhealth_number = ?,
        insurance_number = ?,
        insurance_company = ?,
        emergency_name = ?,
        emergency_relationship = ?,
        emergency_contact = ?
        WHERE id = ? AND is_archived = 0";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error while preparing the update.";
        mysqli_close($conn);

        header("Location: ../clinic/staffs.php");
        exit();
    }

    // ==========================
    // BIND PARAMETERS
    // ==========================

    mysqli_stmt_bind_param(
        $stmt,
        "sddssssssssssi",
        $blood_type,
        $height,
        $weight,
        $allergies,
        $medical_history,
        $current_medications,
        $vaccines,
        $philhealth_number,
        $insurance_number,
        $insurance_company,
        $emergency_name,
        $emergency_relationship,
        $emergency_contact,
        $staffId
    );


    // ==========================
    // EXECUTE UPDATE
    // ==========================

    if (mysqli_stmt_execute($stmt)) {

        /* Clinic Activity Log */
        if (!empty($changes)) {

            $staffName = trim(
                $oldStaff['last_name'] . ', ' . $oldStaff['first_name']
            );

            $staff_id = $oldStaff['staff_id_number'];

            $description = "Updated staff profile for {$staffName}. "
                        . "Staff ID: {$staff_id}. "
                        . "Edited the " . implode('; ', $changes) . ".";

            logClinicActivity(
                $conn,
                'Updated',
                'Staffs',
                $description
            );
        }

        $_SESSION['success'] = "Staff Profile Updated!";
        $_SESSION['success_message'] = "The staff record has been updated successfully.";

    } else {

        $_SESSION['error'] = "Failed to update staff profile.";

    }


    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/staffs.php");
    exit();

} else {

    header("Location: ../clinic/staffs.php");
    exit();

}
?>
