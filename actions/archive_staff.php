<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'Clinic Staff') {
    header("Location: ../clinic/staffs.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../clinic/staffs.php");
    exit();
}

$staff_id = trim($_POST['id'] ?? '');

if ($staff_id === '') {
    $_SESSION['error'] = "Failed to archive staff profile.";
    header("Location: ../clinic/staffs.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE staffs
     SET is_archived = 1
     WHERE staff_id_number = ?
       AND is_archived = 0"
);

if (!$stmt) {
    $_SESSION['error'] = "Failed to archive staff profile.";
} else {
    mysqli_stmt_bind_param($stmt, "s", $staff_id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1) {
        $_SESSION['success'] = "Staff Profile Archived!";
        $_SESSION['success_message'] =
            "The staff profile has been archived successfully. Its records and linked account have been preserved.";
    } else {
        $_SESSION['error'] = "Failed to archive staff profile.";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

header("Location: ../clinic/staffs.php");
exit();
