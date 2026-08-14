<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Staff ID
    $staff_id = $_POST['staff_id_number'];

    // Personal Information
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $position = $_POST['position'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    // Health Information
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

    $sql = "UPDATE staffs SET
        full_name = ?,
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
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        header("Location: ../pages/staffs.php");
        exit();
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssssddssssssss",
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
        $emergency_contact,
        $staff_id
    );

    if (mysqli_stmt_execute($stmt)) {

        $_SESSION['success'] = "Staff profile updated successfully.";

    } else {

        $_SESSION['error'] = "Failed to update staff profile.";

    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../pages/staffs.php");
    exit();

} else {

    header("Location: ../pages/staffs.php");
    exit();

}