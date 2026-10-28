<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: ../clinic/students.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Student ID (Hidden Input)
    $student_id = $_POST['student_id_number'];

    // Name Information

    $last_name = trim($_POST['last_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');

    // Personal Information
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $department = $_POST['department'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    // Health Information
    $blood_type = trim($_POST['blood_type'] ?? '') ?: 'Unknown';

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


    $sql = "UPDATE students SET

        last_name = ?,
        first_name = ?,
        middle_name = ?,
        date_of_birth = ?,
        sex = ?,
        course = ?,
        year_level = ?,
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
        WHERE student_id_number = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        header("Location: ../clinic/students.php");
        exit();
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssissssddsssssssssss",
        $last_name,
        $first_name,
        $middle_name,
        $date_of_birth,
        $sex,
        $course,
        $year_level,
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
        $student_id
    );

    if (mysqli_stmt_execute($stmt)) {

        $_SESSION['success'] = "Student Profile Updated!";
        $_SESSION['success_message'] = "The student record has been updated successfully.";

    } else {

        $_SESSION['error'] = "Failed to update student profile.";

    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/students.php");
    exit();

} else {

    header("Location: ../clinic/students.php");
    exit();

}