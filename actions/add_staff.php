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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $staff_id = $_POST['staff_id_number'];
    $last_name = mb_convert_case(trim($_POST['last_name'] ?? ''), MB_CASE_TITLE, 'UTF-8');
    $first_name = mb_convert_case(trim($_POST['first_name'] ?? ''), MB_CASE_TITLE, 'UTF-8');
    $middle_name = mb_convert_case(trim($_POST['middle_name'] ?? ''), MB_CASE_TITLE, 'UTF-8');
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $position = $_POST['position'];
    $designation = $_POST['designation'] ?? '';
    $department = $_POST['department'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    
    // Medical Information
    $blood_type = trim($_POST['blood_type'] ?? '');

    if ($blood_type === '') {
        $blood_type = null;
    }

    $height = trim($_POST['height'] ?? '') !== ''
        ? trim($_POST['height'])
        : null;

    $weight = trim($_POST['weight'] ?? '') !== ''
        ? trim($_POST['weight'])
        : null;

    $allergies = trim($_POST['allergies'] ?? '') !== ''
        ? trim($_POST['allergies'])
        : null;

    $medical_history = trim($_POST['medical_history'] ?? '') !== ''
        ? trim($_POST['medical_history'])
        : null;

    $current_medications = trim($_POST['current_medications'] ?? '') !== ''
        ? trim($_POST['current_medications'])
        : null;

    $vaccines = trim($_POST['vaccines'] ?? '') !== ''
        ? trim($_POST['vaccines'])
        : null;

    // Insurance Information
    $philhealth_number = trim($_POST['philhealth_number'] ?? '') !== ''
        ? trim($_POST['philhealth_number'])
        : null;

    $insurance_number = trim($_POST['insurance_number'] ?? '') !== ''
        ? trim($_POST['insurance_number'])
        : null;

    $insurance_company = trim($_POST['insurance_company'] ?? '') !== ''
        ? trim($_POST['insurance_company'])
        : null;


    // Emergency Contact
    $emergency_name = $_POST['emergency_name'];
    $emergency_relationship = $_POST['emergency_relationship'];
    $emergency_contact = $_POST['emergency_contact'];

        // Check if Staff ID already exists
    $check = mysqli_prepare($conn,
        "SELECT staff_id_number FROM staffs WHERE staff_id_number = ?"
    );

    mysqli_stmt_bind_param($check, "s", $staff_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {

        $_SESSION['error'] = "Staff ID already exists.";

        mysqli_stmt_close($check);
        mysqli_close($conn);

        header("Location: ../clinic/add_staff.php");
        exit();
    }

    mysqli_stmt_close($check);


    $sql = "INSERT INTO staffs (
        staff_id_number,
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
    ) VALUES (
        ?,?,?,?,?,?,?,?,?,?,?,NULLIF(?, ''),?,?,?,?,?,?,?,?,?,?,?,?
    )";


    $stmt = mysqli_prepare($conn, $sql);


    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssddsssssssssss",
        $staff_id,
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
        $emergency_contact
    );

    if (mysqli_stmt_execute($stmt)) {

    $_SESSION['success'] = "Staff Added!";
    $_SESSION['success_message'] = "The staff record has been added successfully.";

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/staffs.php");
    exit();

} else {

    $_SESSION['error'] = "Failed to add staff profile.";

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/add_staff.php");
    exit();
}
}
?>