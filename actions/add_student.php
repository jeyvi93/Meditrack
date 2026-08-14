<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Academic Information
    $student_id = trim($_POST['student_id_number']);
    $course = trim($_POST['course']);
    $department = trim($_POST['department']);
    $year_level = $_POST['year_level'];

    // Personal Information
    $full_name = trim($_POST['full_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);

    // Medical Information
    $blood_type = trim($_POST['blood_type']);
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $allergies = trim($_POST['allergies']);
    $medical_history = trim($_POST['medical_history']);
    $current_medications = trim($_POST['current_medications']);
    $vaccines = trim($_POST['vaccines']);

    // Insurance Information
    $philhealth_number = trim($_POST['philhealth_number']);
    $insurance_number = trim($_POST['insurance_number']);
    $insurance_company = trim($_POST['insurance_company']);

    // Emergency Contact
    $emergency_name = trim($_POST['emergency_name']);
    $emergency_relationship = trim($_POST['emergency_relationship']);
    $emergency_contact = trim($_POST['emergency_contact']);


    // Check if Student ID already exists
    $check = mysqli_prepare($conn, 
        "SELECT student_id_number FROM students WHERE student_id_number = ?"
    );

    mysqli_stmt_bind_param($check, "s", $student_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);


    if (mysqli_stmt_num_rows($check) > 0) {

        $_SESSION['error'] = "Student ID already exists.";

        mysqli_stmt_close($check);
        mysqli_close($conn);

        header("Location: ../pages/add_student.php");
        exit();
    }

    mysqli_stmt_close($check);


    // Insert Student
    $sql = "INSERT INTO students (
        student_id_number,
        full_name,
        date_of_birth,
        sex,
        course,
        year_level,
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
        "sssssissssssssssssssss",
        $student_id,
        $full_name,
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
        $emergency_contact
    );


    if (mysqli_stmt_execute($stmt)) {

        $_SESSION['success'] = "Student added successfully.";

    } else {

        $_SESSION['error'] = "Failed to add student.";

    }


    mysqli_stmt_close($stmt);
    mysqli_close($conn);


    header("Location: ../pages/students.php");
    exit();


} else {

    header("Location: ../pages/add_student.php");
    exit();

}