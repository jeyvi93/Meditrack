<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

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

/* Get archived record details for the activity log */
$identifierColumn = $type === 'students'
    ? 'student_id_number'
    : 'staff_id_number';

$recordSql = "
    SELECT first_name, last_name, {$identifierColumn} AS record_number
    FROM {$table}
    WHERE id = ? AND is_archived = 1
";

$recordStmt = mysqli_prepare($conn, $recordSql);

$recordName = "Unknown record";
$recordNumber = "Unknown";

if ($recordStmt) {
    mysqli_stmt_bind_param($recordStmt, "i", $id);
    mysqli_stmt_execute($recordStmt);

    $recordResult = mysqli_stmt_get_result($recordStmt);
    $record = mysqli_fetch_assoc($recordResult);

    if ($record) {
        $recordName = trim($record['first_name'] . ' ' . $record['last_name']);
        $recordNumber = $record['record_number'];
    }

    mysqli_stmt_close($recordStmt);
}

$sql = "UPDATE $table SET is_archived = 0 WHERE id = ? AND is_archived = 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['error'] = true;
    $_SESSION['error_message'] = "Unable to restore the record.";
} else {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 1) {

        $module = $type === 'students' ? 'Students' : 'Staffs';

        logClinicActivity(
            $conn,
            'Restored',
            $module,
            "Restored {$type} profile for {$recordName}. ID: {$recordNumber}."
        );

        $_SESSION['success'] = "Record Restored Successfully!";
        $_SESSION['success_message'] = "The archived record has been restored successfully.";

    } elseif (mysqli_stmt_errno($stmt) !== 0) {

        error_log("Restore Record Error: " . mysqli_stmt_error($stmt));

        $_SESSION['error'] = "Unable to restore the record.";

    } else {

        $_SESSION['error'] = "Record was not found or is already restored.";
    }

    mysqli_stmt_close($stmt);
}

header("Location: ../clinic/archived_records.php?tab=" . urlencode($type));
exit();
?>