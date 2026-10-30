<?php

session_start();

require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

// Only allow authorized roles to archive students
$role = $_SESSION['role'] ?? '';

if (!in_array($role, [ 'School Nurse'], true)) {
    http_response_code(403);
    exit("Access denied.");
}

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $_SESSION['error'] = "Invalid student record.";
    header("Location: ../clinic/students.php");
    exit();
}

/* Get student details for the activity log */
$nameSql = "
    SELECT first_name, last_name, student_id_number
    FROM students
    WHERE id = ? AND is_archived = 0
";

$nameStmt = mysqli_prepare($conn, $nameSql);

mysqli_stmt_bind_param($nameStmt, "i", $id);
mysqli_stmt_execute($nameStmt);

$nameResult = mysqli_stmt_get_result($nameStmt);
$student = mysqli_fetch_assoc($nameResult);

$studentName = $student
    ? trim($student['first_name'] . ' ' . $student['last_name'])
    : 'Unknown student';

$studentIdNumber = $student['student_id_number'] ?? 'Unknown';

mysqli_stmt_close($nameStmt);

// Archive instead of permanently deleting the student
$sql = "UPDATE students
        SET is_archived = 1
        WHERE id = ? AND is_archived = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows === 1) {

    logClinicActivity(
        $conn,
        'Archived',
        'Students',
        "Archived student profile for {$studentName}. Student ID: {$studentIdNumber}."
    );

    $_SESSION['success'] = "Student archived successfully.";
} else {
    $_SESSION['error'] = "Unable to archive the student. The record may already be archived.";
}

$stmt->close();

header("Location: ../clinic/students.php");
exit();