<?php

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../login.php");
    exit();
}

$email = trim($_POST["email"] ?? "");

if (empty($email)) {
    header("Location: ../../login.php?error=resend_failed");
    exit();
}


/*
|--------------------------------------------------------------------------
| Find User
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, full_name, email, email_verified
        FROM users
        WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    header("Location: ../../login.php?error=resend_failed");
    exit();
}

$user = mysqli_fetch_assoc($result);


/*
|--------------------------------------------------------------------------
| Check If Already Verified
|--------------------------------------------------------------------------
*/

if ((int)$user["email_verified"] === 1) {
    header("Location: ../../login.php?error=already_verified");
    exit();
}


/*
|--------------------------------------------------------------------------
| Generate New Verification Token
|--------------------------------------------------------------------------
*/

$verification_token = bin2hex(random_bytes(32));

$verification_expires = date(
    "Y-m-d H:i:s",
    time() + 900
);


/*
|--------------------------------------------------------------------------
| Update Verification Token
|--------------------------------------------------------------------------
*/

$sql = "UPDATE users
        SET verification_token = ?,
            verification_expires = ?
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $verification_token,
    $verification_expires,
    $user["id"]
);

if (!mysqli_stmt_execute($stmt)) {
    header("Location: ../../login.php?error=resend_failed");
    exit();
}


/*
|--------------------------------------------------------------------------
| Verification Link
|--------------------------------------------------------------------------
*/

$verification_link =
    "http://localhost/MediTrack%20Clinic/api/auth/verify_email.php?token=" .
    urlencode($verification_token);


/*
|--------------------------------------------------------------------------
| Send Verification Email
|--------------------------------------------------------------------------
*/

$mailConfig = require "../../config/mail.php";

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();

    $mail->Host       = $mailConfig["host"];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig["username"];
    $mail->Password   = $mailConfig["password"];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $mailConfig["port"];

    $mail->setFrom(
        $mailConfig["username"],
        "MediTrack Clinic"
    );

    $mail->addAddress(
        $user["email"],
        $user["full_name"]
    );

    $mail->isHTML(true);

    $mail->Subject = "New MediTrack Verification Link";

    $mail->Body = "
        <h2>MediTrack Clinic</h2>

        <p>
            Hello <strong>" . htmlspecialchars($user["full_name"]) . "</strong>,
        </p>

        <p>
            A new email verification link has been requested for
            your MediTrack Clinic account.
        </p>

        <p>
            Please click the button below to verify your email address.
        </p>

        <p style='margin: 30px 0;'>
            <a href='" . $verification_link . "'
            style='
            display:inline-block;
            padding:12px 22px;
            background:#800000;
            color:white;
            text-decoration:none;
            border-radius:6px;
            font-weight:bold;'>
            Verify Email Address
            </a>
        </p>

        <p>
            This verification link will expire in
            <strong>15 minutes</strong>.
        </p>

        <p>
            If you did not request a new verification link,
            you can safely ignore this email.
        </p>

        <p style='margin-top: 30px;'>
            Regards,<br>
            <strong>MediTrack Clinic</strong>
        </p>
    ";

    $mail->send();

    header("Location: ../../login.php?resend=success");
    exit();

} catch (Exception $e) {

    header("Location: ../../login.php?error=resend_failed");
    exit();
}

?>
