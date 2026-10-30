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

require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

if (isset($_GET['id'])) {

    $studentId = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($studentId === false || $studentId <= 0) {
        $_SESSION['error'] = "Invalid student record.";
    } else {
        /* Get student details before deletion */
        $nameSql = "
            SELECT first_name, last_name, student_id_number
            FROM students
            WHERE id = ?
        ";

        $nameStmt = mysqli_prepare($conn, $nameSql);

        $studentName = "Unknown student";
        $studentIdNumber = "Unknown";

        if ($nameStmt) {
            mysqli_stmt_bind_param($nameStmt, "i", $studentId);
            mysqli_stmt_execute($nameStmt);

            $nameResult = mysqli_stmt_get_result($nameStmt);
            $student = mysqli_fetch_assoc($nameResult);

            if ($student) {
                $studentName = trim(
                    $student['first_name'] . ' ' . $student['last_name']
                );

                $studentIdNumber = $student['student_id_number'];
            }

            mysqli_stmt_close($nameStmt);
        }
        $sql = "DELETE FROM students WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log("Student Delete Prepare Error: " . mysqli_error($conn));
            $_SESSION['error'] = "Failed to delete student profile.";

        } else {

            mysqli_stmt_bind_param($stmt, "i", $studentId);

            $executed = mysqli_stmt_execute($stmt);
            $affectedRows = mysqli_stmt_affected_rows($stmt);

            if ($executed && $affectedRows === 1) {

                logClinicActivity(
                    $conn,
                    'Deleted',
                    'Students',
                    "Deleted student profile for {$studentName}. Student ID: {$studentIdNumber}."
                );

                $_SESSION['success'] = "Student Profile Deleted!";
                $_SESSION['success_message'] = "The student record has been deleted successfully.";

            } else {

                if (!$executed) {
                    error_log("Student Delete Error: " . mysqli_stmt_error($stmt));
                    $_SESSION['error'] = "Failed to delete student profile. The record may be linked to existing clinic records.";
                } else {
                    $_SESSION['error'] = "Student record was not found or has already been deleted.";
                }
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}

header("Location: ../clinic/students.php");
exit();