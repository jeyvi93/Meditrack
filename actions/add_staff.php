<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $staff_id = $_POST['staff_id_number'];
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $position = $_POST['position'];
    $designation = $_POST['designation'] ?? '';
    $department = $_POST['department'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    // Medical Information
    $blood_type = $_POST['blood_type'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $allergies = trim($_POST['allergies'] ?? '') ?: 'N/A';
    $medical_history = trim($_POST['medical_history'] ?? '') ?: 'N/A';
    $current_medications = trim($_POST['current_medications'] ?? '') ?: 'N/A';
    $vaccines = trim($_POST['vaccines'] ?? '') ?: 'N/A';

    // Insurance Information
    $philhealth_number = trim($_POST['philhealth_number'] ?? '') ?: 'N/A';
    $insurance_number = trim($_POST['insurance_number'] ?? '') ?: 'N/A';
    $insurance_company = trim($_POST['insurance_company'] ?? '') ?: 'N/A';

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
        full_name,
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
        ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
    )";


    $stmt = mysqli_prepare($conn, $sql);


    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssddssssssssss",
        $staff_id,
        $full_name,
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

    $_SESSION['success'] = "Staff profile added successfully.";

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