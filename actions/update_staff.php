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

    $blood_type = trim($_POST['blood_type'] ?? '') ?: 'Unknown';

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
