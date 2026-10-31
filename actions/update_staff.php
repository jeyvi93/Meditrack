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

    // ==========================
    // STAFF ID
    // ==========================

    $staff_id = trim($_POST['staff_id_number'] ?? '');



    // ==========================
    // PERSONAL INFORMATION
    // ==========================

    $last_name = trim($_POST['last_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $position = $_POST['position'] ?? '';
    $designation = trim($_POST['designation'] ?? '');
    $department = $_POST['department'] ?? '';
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');


    // ==========================
    // NAME VALIDATION
    // ==========================

    if ($first_name === '' || $last_name === '') {
        $_SESSION['error'] = "First name and last name are required.";
        header("Location: ../clinic/staffs.php");
        exit();
    }

    if (
        !preg_match("/^[A-Za-zÀ-ÿ\s.,'-]+$/", $first_name) ||
        !preg_match("/^[A-Za-zÀ-ÿ\s.,'-]+$/", $last_name) ||
        ($middle_name !== '' &&
            !preg_match("/^[A-Za-zÀ-ÿ\s.,'-]+$/", $middle_name))
    ) {
        $_SESSION['error'] = "Names should contain letters, spaces, and common name punctuation only.";
        header("Location: ../clinic/staffs.php");
        exit();
    }


    // ==========================
    // DATE OF BIRTH VALIDATION
    // ==========================

    if ($date_of_birth === '') {
        $_SESSION['error'] = "Date of birth is required.";
        header("Location: ../clinic/staffs.php");
        exit();
    }


    // ==========================
    // POSITION / DESIGNATION / DEPARTMENT
    // ==========================

    $allowedPositions = [
        "Teaching Staff",
        "Non-Teaching Staff"
    ];

    if (!in_array($position, $allowedPositions, true)) {
        $_SESSION['error'] = "Please select a valid position.";
        header("Location: ../clinic/staffs.php");
        exit();
    }

    if ($designation === '') {
        $_SESSION['error'] = "Designation is required.";
        header("Location: ../clinic/staffs.php");
        exit();
    }

    $allowedDepartments = [
        "Technology",
        "Education",
        "Business",
        "Clinic",
        "Library",
        "Guidance Office",
        "Cashier",
        "Registrar",
        "Utility",
        "Admin Office",
        "SASO",
        "CSU"
    ];

    if (!in_array($department, $allowedDepartments, true)) {
        $_SESSION['error'] = "Please select a valid department.";
        header("Location: ../clinic/staffs.php");
        exit();
    }


    // ==========================
    // CONTACT NUMBER
    // ==========================

    if (!preg_match('/^09[0-9]{9}$/', $contact_number)) {
        $_SESSION['error'] = "Contact number must contain exactly 11 digits and start with 09.";
        header("Location: ../clinic/staffs.php");
        exit();
    }


    // ==========================
    // HEALTH INFORMATION
    // ==========================

    $blood_type = trim($_POST['blood_type'] ?? '') !== ''
    ? trim($_POST['blood_type'])
    : null;

    $height = trim($_POST['height'] ?? '') !== ''
        ? trim($_POST['height'])
        : null;

    $weight = trim($_POST['weight'] ?? '') !== ''
        ? trim($_POST['weight'])
        : null;

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


    // Emergency name validation if provided
    if (
        $emergency_name !== '' &&
        !preg_match("/^[A-Za-zÀ-ÿ\s.,'-]+$/", $emergency_name)
    ) {
        $_SESSION['error'] = "Emergency contact name should contain letters, spaces, and common name punctuation only.";
        header("Location: ../clinic/staffs.php");
        exit();
    }


    // Emergency relationship validation if provided
    if (
        $emergency_relationship !== '' &&
        !preg_match("/^[A-Za-zÀ-ÿ\s-]+$/", $emergency_relationship)
    ) {
        $_SESSION['error'] = "Emergency relationship should contain letters and spaces only.";
        header("Location: ../clinic/staffs.php");
        exit();
    }


    // Emergency contact validation if provided
    if (
        $emergency_contact !== '' &&
        !preg_match('/^09[0-9]{9}$/', $emergency_contact)
    ) {
        $_SESSION['error'] = "Emergency contact must contain exactly 11 digits and start with 09.";
        header("Location: ../clinic/staffs.php");
        exit();
    }

    // ==========================
    // GET ORIGINAL STAFF INFORMATION
    // ==========================

    $oldSql = "
        SELECT
            last_name,
            first_name, 
            middle_name,
            date_of_birth,
            sex,
            position,
            designation,
            department,
            contact_number,
            address,
            blood_type,
            height,
            weight,
            allergies,
            medical_history,
            current_medications,
            vaccines,
            philhealth_number,
            insurance_number,
            insurance_company,
            emergency_name,
            emergency_relationship,
            emergency_contact
        FROM staffs
        WHERE staff_id_number = ?
    ";

    $oldStmt = mysqli_prepare($conn, $oldSql);

    if (!$oldStmt) {
        $_SESSION['error'] = "Failed to retrieve original staff information.";
        mysqli_close($conn);
        header("Location: ../clinic/staffs.php");
        exit();
    }

    mysqli_stmt_bind_param($oldStmt, "s", $staff_id);
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
        'last_name'              => 'last name',
        'first_name'             => 'first name',
        'middle_name'            => 'middle name',
        'date_of_birth'          => 'date of birth',
        'sex'                    => 'sex',
        'position'               => 'position',
        'designation'            => 'designation',
        'department'             => 'department',
        'contact_number'         => 'contact number',
        'address'                => 'address',
        'blood_type'             => 'blood type',
        'height'                 => 'height',
        'weight'                 => 'weight',
        'allergies'              => 'allergies',
        'medical_history'        => 'medical history',
        'current_medications'    => 'current medications',
        'vaccines'               => 'vaccines',
        'philhealth_number'      => 'PhilHealth number',
        'insurance_number'       => 'insurance number',
        'insurance_company'     => 'insurance company',
        'emergency_name'         => 'emergency contact name',
        'emergency_relationship' => 'emergency relationship',
        'emergency_contact'      => 'emergency contact number'
    ];

    $newValues = [
        'last_name'              => $last_name,
        'first_name'             => $first_name,
        'middle_name'            => $middle_name,
        'date_of_birth'          => $date_of_birth,
        'sex'                    => $sex,
        'position'               => $position,
        'designation'            => $designation,
        'department'             => $department,
        'contact_number'         => $contact_number,
        'address'                => $address,
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

        // Normalize decimal values to avoid false changes
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

        last_name = ?,
        first_name = ?,
        middle_name = ?,
        date_of_birth = ?,
        sex = ?,
        position = ?,
        designation = ?,
        department = ?,
        contact_number = ?,
        address = ?,
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
        WHERE staff_id_number = ?";

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
        "sssssssssssddsssssssssss",
        $last_name,
        $first_name,
        $middle_name,
        $date_of_birth,
        $sex,
        $position,
        $designation,
        $department,
        $contact_number,
        $address,
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
        $staff_id
    );


    // ==========================
    // EXECUTE UPDATE
    // ==========================

    if (mysqli_stmt_execute($stmt)) {

        /* Clinic Activity Log */
        if (!empty($changes)) {

            $staffName = trim($last_name . ', ' . $first_name);

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
