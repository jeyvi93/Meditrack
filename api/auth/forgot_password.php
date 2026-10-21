<?php

session_start();

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../patient/forgot_password.php");
    exit();
}

$email = trim($_POST["email"] ?? "");

if (empty($email)) {
    header("Location: ../../patient/forgot_password.php?error=1");
    exit();
}


/*
|--------------------------------------------------------------------------
| Find User
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, full_name, email
        FROM users
        WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| Generic Response
|--------------------------------------------------------------------------
|
| We do not tell the user whether the email exists.
|
*/

if (mysqli_num_rows($result) !== 1) {
    header("Location: ../../patient/forgot_password.php?sent=1");
    exit();
}

$user = mysqli_fetch_assoc($result);


/*
|--------------------------------------------------------------------------
| Generate Reset Token
|--------------------------------------------------------------------------
*/

$reset_token = bin2hex(random_bytes(32));

$reset_expires = date(
    "Y-m-d H:i:s",
    time() + 900
);


/*
|--------------------------------------------------------------------------
| Save Reset Token
|--------------------------------------------------------------------------
*/

$sql = "UPDATE users
        SET reset_token = ?,
            reset_expires = ?
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $reset_token,
    $reset_expires,
    $user["id"]
);

if (!mysqli_stmt_execute($stmt)) {
    header("Location: ../../patient/forgot_password.php?sent=1");
    exit();
}

/*
|--------------------------------------------------------------------------
| Reset Link
|--------------------------------------------------------------------------
*/

// Detect HTTPS, including supported proxy/tunnel setups.
$isHttps = (
    (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
    (!empty($_SERVER["HTTP_X_FORWARDED_PROTO"]) &&
     strtolower(trim(explode(",", $_SERVER["HTTP_X_FORWARDED_PROTO"])[0])) === "https")
);

$scheme = $isHttps ? "https" : "http";
$host = $_SERVER["HTTP_HOST"] ?? "localhost";

// Derive the project root from this handler's location.
$projectPath = str_replace(
    "\\",
    "/",
    rtrim(dirname(dirname(dirname($_SERVER["SCRIPT_NAME"]))), "/\\")
);

$reset_link = $scheme . "://" . $host .
    $projectPath .
    "/patient/reset_password.php?token=" .
    urlencode($reset_token);


/*
|--------------------------------------------------------------------------
| Mail Configuration
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


    /*
    |--------------------------------------------------------------------------
    | Sender
    |--------------------------------------------------------------------------
    */

    $mail->setFrom(
        $mailConfig["username"],
        "MediTrack"
    );


    /*
    |--------------------------------------------------------------------------
    | Recipient
    |--------------------------------------------------------------------------
    */

    $mail->addAddress(
        $user["email"],
        $user["full_name"]
    );


    /*
    |--------------------------------------------------------------------------
    | Email Content
    |--------------------------------------------------------------------------
    */

    $mail->isHTML(true);

    $mail->Subject = "MediTrack Password Reset";

    $mail->Body = "
        <h2>MediTrack</h2>

        <p>
            Hello <strong>" .
            htmlspecialchars($user["full_name"]) .
            "</strong>,
        </p>

        <p>
            We received a request to reset the password
            for your MediTrack account.
        </p>

        <p>
            Click the button below to create a new password.
        </p>

        <p style='margin: 30px 0;'>
            <a href='" . $reset_link . "'
            style='
            display:inline-block;
            padding:12px 22px;
            background:#800000;
            color:white;
            text-decoration:none;
            border-radius:6px;
            font-weight:bold;'>
            Reset Password
            </a>
        </p>

        <p>
            This password reset link will expire in
            <strong>15 minutes</strong>.
        </p>

        <p>
            If you did not request a password reset,
            you can safely ignore this email.
        </p>

        <p style='margin-top: 30px;'>
            Regards,<br>
            <strong>MediTrack</strong>
        </p>
    ";


    /*
    |--------------------------------------------------------------------------
    | Send Email
    |--------------------------------------------------------------------------
    */

    $mail->send();

} catch (Exception $e) {

    // Do not expose mail errors to the user.

}


/*
|--------------------------------------------------------------------------
| Return to Forgot Password Page
|--------------------------------------------------------------------------
*/

header("Location: ../../patient/forgot_password.php?sent=1");
exit();

?>