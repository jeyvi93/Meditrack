<?php

session_start();

require_once "../config/database.php";

// ==========================
// CHECK SESSION AND ROLE
// ==========================

$userId = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? '';

$allowedRoles = ['Administrator', 'School Nurse', 'Clinic Staff'];

$redirect = "../clinic/change_personnel_password.php";

if (!$userId || !in_array($role, $allowedRoles, true)) {
    header("Location: ../index.php");
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $redirect");
    exit();
}

// ==========================
// GET FORM DATA
// ==========================

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// ==========================
// VALIDATE INPUT
// ==========================

if (!is_string($currentPassword) || $currentPassword === '') {
    $_SESSION['error'] = "Please enter your current password.";

    header("Location: $redirect");
    exit();
}

if (!is_string($newPassword) || $newPassword === '') {
    $_SESSION['error'] = "Please enter your new password.";

    header("Location: $redirect");
    exit();
}

if (!is_string($confirmPassword) || $confirmPassword === '') {
    $_SESSION['error'] = "Please confirm your new password.";

    header("Location: $redirect");
    exit();
}

// ==========================
// CHECK NEW PASSWORD LENGTH
// ==========================

if (strlen($newPassword) < 8) {
    $_SESSION['error'] = "New password must be at least 8 characters long.";

    header("Location: $redirect");
    exit();
}

// ==========================
// CHECK CONFIRMATION
// ==========================

if ($newPassword !== $confirmPassword) {
    $_SESSION['error'] = "New password and confirmation password do not match.";

    header("Location: $redirect");
    exit();
}

// ==========================
// GET CURRENT PASSWORD
// ==========================

$sql = "
    SELECT password
    FROM users
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['error'] = "Something went wrong. Please try again.";

    header("Location: $redirect");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// ==========================
// CHECK USER
// ==========================

if (!$user) {
    $_SESSION['error'] = "Unable to find your account. Please try again.";

    header("Location: $redirect");
    exit();
}

// ==========================
// VERIFY CURRENT PASSWORD
// ==========================

$hashedPassword = $user['password'];

if (!password_verify($currentPassword, $hashedPassword)) {
    $_SESSION['error'] = "Your current password is incorrect.";

    header("Location: $redirect");
    exit();
}

// ==========================
// PREVENT SAME PASSWORD
// ==========================

if (password_verify($newPassword, $hashedPassword)) {
    $_SESSION['error'] = "Your new password must be different from your current password.";

    header("Location: $redirect");
    exit();
}

// ==========================
// HASH NEW PASSWORD
// ==========================

$newHashedPassword = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
);

if ($newHashedPassword === false) {
    $_SESSION['error'] = "Unable to process your new password. Please try again.";

    header("Location: $redirect");
    exit();
}

// ==========================
// UPDATE PASSWORD
// ==========================

$updateSql = "
    UPDATE users
    SET password = ?
    WHERE id = ?
";

$updateStmt = mysqli_prepare($conn, $updateSql);

if (!$updateStmt) {
    $_SESSION['error'] = "Unable to update your password. Please try again.";

    header("Location: $redirect");
    exit();
}

mysqli_stmt_bind_param(
    $updateStmt,
    "si",
    $newHashedPassword,
    $userId
);

if (mysqli_stmt_execute($updateStmt)) {

    mysqli_stmt_close($updateStmt);
    mysqli_close($conn);

    // Refresh session ID after successful password change
    session_regenerate_id(true);

    $_SESSION['success'] = "Your password has been successfully updated.";

    header("Location: $redirect");
    exit();
}

mysqli_stmt_close($updateStmt);
mysqli_close($conn);

$_SESSION['error'] = "Unable to update your password. Please try again.";

header("Location: $redirect");
exit();
