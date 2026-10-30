<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

$role = $_SESSION['role'] ?? '';

if ($role === 'Clinic Staff') {
    header("Location: ../clinic/dashboard.php");
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../clinic/archived_records.php");
    exit();
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$type = $_POST['type'] ?? '';

if (!$id || !in_array($type, ['students', 'staffs'], true)) {
    $_SESSION['error'] = true;
    $_SESSION['error_message'] = "Invalid archived record.";
    header("Location: ../clinic/archived_records.php");
    exit();
}

$table = $type === 'students' ? 'students' : 'staffs';

$sql = "UPDATE $table SET is_archived = 0 WHERE id = ? AND is_archived = 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['error'] = true;
    $_SESSION['error_message'] = "Unable to restore the record.";
} else {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 1) {
        $_SESSION['success'] = "Record Restored Successfully!";
        $_SESSION['success_message'] = "The archived record has been restored successfully.";
    } else {
        $_SESSION['error'] = "Record was not found or is already restored.";
    }

    mysqli_stmt_close($stmt);
}

header("Location: ../clinic/archived_records.php?tab=" . urlencode($type));
exit();
?>