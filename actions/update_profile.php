<?php

session_start();

require_once "../config/database.php";
require_once "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$userId = $_SESSION['user_id'] ?? 0;

if (!$userId) {
    header("Location: ../login.php");
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
// GET CURRENT USER
// ==========================

$sql = "SELECT full_name, email
        FROM users
        WHERE id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['error'] = "Something went wrong. Please try again.";
    header("Location: ../patient/edit_profile.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$currentUser = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$currentUser) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$currentEmail = $currentUser['email'];


// ==========================
// CHECK DUPLICATE EMAIL
// ==========================

if ($email !== $currentEmail) {

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

        $_SESSION['error'] =
            "That email address is already being used by another account.";

        header("Location: ../patient/edit_profile.php");
        exit();
    }

    mysqli_stmt_close($checkStmt);


    // Also check pending email addresses.
    $pendingSql = "
        SELECT id
        FROM users
        WHERE pending_email = ?
          AND id != ?
        LIMIT 1
    ";

    $pendingStmt = mysqli_prepare($conn, $pendingSql);

    if (!$pendingStmt) {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: ../patient/edit_profile.php");
        exit();
    }

    mysqli_stmt_bind_param($pendingStmt, "si", $email, $userId);
    mysqli_stmt_execute($pendingStmt);

    $pendingResult = mysqli_stmt_get_result($pendingStmt);

    if (mysqli_num_rows($pendingResult) > 0) {
        mysqli_stmt_close($pendingStmt);

        $_SESSION['error'] =
            "That email address is already awaiting verification for another account.";

        header("Location: ../patient/edit_profile.php");
        exit();
    }

    mysqli_stmt_close($pendingStmt);
}


// ==========================
// UPDATE NAME ONLY IF EMAIL IS CHANGING
// ==========================

if ($email !== $currentEmail) {

    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", time() + 900);

    // Save the name and pending email.
    // The active email remains unchanged until verification.
    $updateSql = "
        UPDATE users
        SET full_name = ?,
            pending_email = ?,
            email_change_token = ?,
            email_change_expires = ?
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
        "ssssi",
        $fullName,
        $email,
        $token,
        $expires,
        $userId
    );

    if (!mysqli_stmt_execute($updateStmt)) {
        mysqli_stmt_close($updateStmt);

        $_SESSION['error'] = "Unable to update your profile. Please try again.";
        header("Location: ../patient/edit_profile.php");
        exit();
    }

    mysqli_stmt_close($updateStmt);


    // ==========================
    // BUILD VERIFICATION LINK
    // ==========================

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (
            !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0])) === 'https'
        )
    );

    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $projectPath = str_replace(
        '\\',
        '/',
        rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\')
    );

    $verificationLink = $scheme . '://' . $host .
        $projectPath .
        '/api/auth/verify_email_change.php?token=' .
        urlencode($token);


    // ==========================
    // SEND VERIFICATION EMAIL
    // ==========================

    $mailConfig = require "../config/mail.php";
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'];
        $mail->Password   = $mailConfig['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailConfig['port'];

        $mail->setFrom($mailConfig['username'], "MediTrack");

        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = "Verify Your New MediTrack Email";

        $safeName = htmlspecialchars(
            $fullName,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $safeLink = htmlspecialchars(
            $verificationLink,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $mail->Body = "
            <h2>MediTrack</h2>

            <p>Hello <strong>{$safeName}</strong>,</p>

            <p>
                You requested to change the email address associated
                with your MediTrack account.
            </p>

            <p>
                Click the button below to verify your new email address.
                Your current email address will remain active until
                this verification is completed.
            </p>

            <p style='margin: 30px 0;'>
                <a href='{$safeLink}'
                   style='display:inline-block;padding:12px 22px;
                   background:#800000;color:white;text-decoration:none;
                   border-radius:6px;font-weight:bold;'>
                   Verify New Email
                </a>
            </p>

            <p>
                This verification link will expire in
                <strong>15 minutes</strong>.
            </p>

            <p>
                If you did not request this change, you can ignore
                this email. Your current email address will remain active.
            </p>

            <p style='margin-top:30px;'>
                Regards,<br><strong>MediTrack</strong>
            </p>
        ";

        $mail->send();

    } catch (Exception $e) {

        // Clear the pending request if sending fails.
        $clearSql = "
            UPDATE users
            SET pending_email = NULL,
                email_change_token = NULL,
                email_change_expires = NULL
            WHERE id = ?
              AND email_change_token = ?
        ";

        $clearStmt = mysqli_prepare($conn, $clearSql);

        if ($clearStmt) {
            mysqli_stmt_bind_param($clearStmt, "is", $userId, $token);
            mysqli_stmt_execute($clearStmt);
            mysqli_stmt_close($clearStmt);
        }

        $_SESSION['full_name'] = $fullName;

        $_SESSION['error'] =
            "Your name was updated, but we couldn't send the verification email. Please try changing your email again.";

        header("Location: ../patient/edit_profile.php");
        exit();
    }


    // Update only the name in the current session.
    // Keep the old email in the session until verified.
    $_SESSION['full_name'] = $fullName;

    $_SESSION['success'] =
        "A verification link has been sent to your new email address. Your email will change after verification.";

    header("Location: ../patient/edit_profile.php");
    exit();
}


// ==========================
// EMAIL UNCHANGED: UPDATE NAME
// ==========================

$updateSql = "
    UPDATE users
    SET full_name = ?
    WHERE id = ?
";

$updateStmt = mysqli_prepare($conn, $updateSql);

if (!$updateStmt) {
    $_SESSION['error'] = "Unable to update your profile. Please try again.";
    header("Location: ../patient/edit_profile.php");
    exit();
}

mysqli_stmt_bind_param($updateStmt, "si", $fullName, $userId);

if (mysqli_stmt_execute($updateStmt)) {

    $_SESSION['full_name'] = $fullName;

    mysqli_stmt_close($updateStmt);

    $_SESSION['success'] =
        "Your profile information has been successfully updated.";

    header("Location: ../patient/edit_profile.php");
    exit();
}

mysqli_stmt_close($updateStmt);

$_SESSION['error'] = "Unable to update your profile. Please try again.";
header("Location: ../patient/edit_profile.php");
exit();

?>
