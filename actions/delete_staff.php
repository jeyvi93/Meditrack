<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: ../clinic/staffs.php");
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

    $staff_id = $_GET['id'];

    /* Get staff details before deletion */
    $nameSql = "
        SELECT first_name, last_name
        FROM staffs
        WHERE staff_id_number = ?
    ";

    $nameStmt = mysqli_prepare($conn, $nameSql);

    $staffName = "Unknown staff";

    if ($nameStmt) {
        mysqli_stmt_bind_param($nameStmt, "s", $staff_id);
        mysqli_stmt_execute($nameStmt);

        $nameResult = mysqli_stmt_get_result($nameStmt);
        $staff = mysqli_fetch_assoc($nameResult);

        if ($staff) {
            $staffName = trim($staff['first_name'] . ' ' . $staff['last_name']);
        }

        mysqli_stmt_close($nameStmt);
    }

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM staffs WHERE staff_id_number = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $staff_id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1) {

    logClinicActivity(
            $conn,
            'Deleted',
            'Staffs',
            "Deleted staff profile for {$staffName}. Staff ID: {$staff_id}."
        );

        $_SESSION['success'] = "Staff Profile Deleted!";
        $_SESSION['success_message'] = "The staff record has been deleted successfully.";

    } else {

        $_SESSION['error'] = "Failed to delete staff profile.";

    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

header("Location: ../clinic/staffs.php");
exit();