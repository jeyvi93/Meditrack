<?php

session_start();

require_once "../config/database.php";

$userId = $_SESSION['user_id'] ?? 0;

if (!$userId) {
    header("Location: ../login.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'Administrator') {
    header("Location: ../admin/accounts.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'Clinic Staff') {
    header("Location: ../clinic/dashboard.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'School Nurse') {
    header("Location: ../clinic/dashboard.php");
    exit();
}


// ==========================
// GET FORM DATA
// ==========================

$currentPassword = trim($_POST['current_password'] ?? '');
$newPassword = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');


// ==========================
// VALIDATE INPUT
// ==========================

if (
    $currentPassword === '' ||
    $newPassword === '' ||
    $confirmPassword === ''
) {

    $_SESSION['error'] = "All password fields are required.";

    header("Location: ../patient/change_password.php");
    exit();
}


// ==========================
// CHECK NEW PASSWORD LENGTH
// ==========================

if (strlen($newPassword) < 8) {

    $_SESSION['error'] = "New password must be at least 8 characters long.";

    header("Location: ../patient/change_password.php");
    exit();
}


// ==========================
// CHECK PASSWORD CONFIRMATION
// ==========================

if ($newPassword !== $confirmPassword) {

    $_SESSION['error'] = "New password and confirmation password do not match.";

    header("Location: ../patient/change_password.php");
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

    header("Location: ../patient/change_password.php");
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

    header("Location: ../patient/change_password.php");
    exit();
}

$hashedPassword = $user['password'];


// ==========================
// VERIFY CURRENT PASSWORD
// ==========================

if (!password_verify($currentPassword, $hashedPassword)) {

    $_SESSION['error'] = "Your current password is incorrect.";

    header("Location: ../patient/change_password.php");
    exit();
}


// ==========================
// PREVENT SAME PASSWORD
// ==========================

if (password_verify($newPassword, $hashedPassword)) {

    $_SESSION['error'] = "Your new password must be different from your current password.";

    header("Location: ../patient/change_password.php");
    exit();
}


// ==========================
// HASH NEW PASSWORD
// ==========================

$newHashedPassword = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
);


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

    header("Location: ../patient/change_password.php");
    exit();
}

mysqli_stmt_bind_param(
    $updateStmt,
    "si",
    $newHashedPassword,
    $userId
);


// ==========================
// EXECUTE UPDATE
// ==========================

if (mysqli_stmt_execute($updateStmt)) {

    mysqli_stmt_close($updateStmt);

    $_SESSION['success'] = "Your password has been successfully updated.";

    header("Location: ../patient/change_password.php");
    exit();
}


// ==========================
// UPDATE FAILED
// ==========================

mysqli_stmt_close($updateStmt);

$_SESSION['error'] = "Unable to update your password. Please try again.";

header("Location: ../patient/change_password.php");
exit();

?>