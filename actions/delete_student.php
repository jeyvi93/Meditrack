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

if (isset($_GET['id'])) {

    $student_id = $_GET['id'];

    $sql = "DELETE FROM students WHERE student_id_number = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $student_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Student Profile Deleted!";
        $_SESSION['success_message'] = "The student record has been deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete student profile.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

header("Location: ../clinic/students.php");
exit();