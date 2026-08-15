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
    $allergies = $_POST['allergies'];
    $medical_history = $_POST['medical_history'];
    $current_medications = $_POST['current_medications'];
    $vaccines = $_POST['vaccines'];

    // Insurance Information
    $philhealth_number = $_POST['philhealth_number'];
    $insurance_number = $_POST['insurance_number'];
    $insurance_company = $_POST['insurance_company'];

    // Emergency Contact
    $emergency_name = $_POST['emergency_name'];
    $emergency_relationship = $_POST['emergency_relationship'];
    $emergency_contact = $_POST['emergency_contact'];


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
        "sssssssssddsssssssssss",
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

    } else {

        $_SESSION['error'] = "Failed to add staff profile.";

    }


    mysqli_stmt_close($stmt);
    mysqli_close($conn);


    header("Location: ../clinic/staffs.php");
    exit();

}
?>