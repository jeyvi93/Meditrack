<?php

session_start();

require_once "../config/database.php";

$userId = $_SESSION['user_id'] ?? 0;

if (!$userId) {
    header("Location: ../auth/login.php");
    exit();
}


// ==========================
// GET FORM DATA
// ==========================

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');


// ==========================
// VALIDATE INPUT
// ==========================

if ($fullName === '' || $email === '') {

    $_SESSION['error'] = "Full name and email address are required.";

    header("Location: ../patient/edit_profile.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION['error'] = "Please enter a valid email address.";

    header("Location: ../patient/edit_profile.php");
    exit();
}


// ==========================
// CHECK DUPLICATE EMAIL
// ==========================

$checkSql = "
    SELECT id
    FROM users
    WHERE email = ?
    AND id != ?
    LIMIT 1
";

$checkStmt = mysqli_prepare($conn, $checkSql);

if (!$checkStmt) {

    $_SESSION['error'] = "Something went wrong. Please try again.";

    header("Location: ../patient/edit_profile.php");
    exit();
}

mysqli_stmt_bind_param($checkStmt, "si", $email, $userId);

mysqli_stmt_execute($checkStmt);

$checkResult = mysqli_stmt_get_result($checkStmt);

if (mysqli_num_rows($checkResult) > 0) {

    mysqli_stmt_close($checkStmt);

    $_SESSION['error'] = "That email address is already being used by another account.";

    header("Location: ../patient/edit_profile.php");
    exit();
}

mysqli_stmt_close($checkStmt);


// ==========================
// UPDATE USER PROFILE
// ==========================

$updateSql = "
    UPDATE users
    SET full_name = ?, email = ?
    WHERE id = ?
";

$updateStmt = mysqli_prepare($conn, $updateSql);

if (!$updateStmt) {

    $_SESSION['error'] = "Unable to update your profile. Please try again.";

    header("Location: ../patient/edit_profile.php");
    exit();
}

mysqli_stmt_bind_param(
    $updateStmt,
    "ssi",
    $fullName,
    $email,
    $userId
);


// ==========================
// EXECUTE UPDATE
// ==========================

if (mysqli_stmt_execute($updateStmt)) {

    // Update session name so the new name
    // is immediately reflected throughout the system.
    $_SESSION['full_name'] = $fullName;

    mysqli_stmt_close($updateStmt);

    $_SESSION['success'] = "Your profile information has been successfully updated.";

    header("Location: ../patient/edit_profile.php");
    exit();
}


// ==========================
// UPDATE FAILED
// ==========================

mysqli_stmt_close($updateStmt);

$_SESSION['error'] = "Unable to update your profile. Please try again.";

header("Location: ../patient/edit_profile.php");
exit();
?>
