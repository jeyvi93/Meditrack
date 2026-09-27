<?php

require_once "../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}


$token = $_GET["token"] ?? "";

if (empty($token)) {

    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}


// Find user with this verification token
$sql = "SELECT id, email_verified, verification_expires
        FROM users
        WHERE verification_token = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) !== 1) {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}


$user = mysqli_fetch_assoc($result);


// Check expiration
if (
    empty($user["verification_expires"]) ||
    strtotime($user["verification_expires"]) < time()
) {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../../patient/verification_result.php?status=expired");
    exit();
}


// Verify the email
$update = mysqli_prepare(
    $conn,
    "UPDATE users
     SET email_verified = 1,
         verification_token = NULL,
         verification_expires = NULL
     WHERE id = ?"
);

mysqli_stmt_bind_param($update, "i", $user["id"]);


if (mysqli_stmt_execute($update)) {

    mysqli_stmt_close($stmt);
    mysqli_stmt_close($update);
    mysqli_close($conn);

    header("Location: ../../patient/verification_result.php?status=success");
    exit();

}


mysqli_stmt_close($stmt);
mysqli_stmt_close($update);
mysqli_close($conn);

header("Location: ../../patient/verification_result.php?status=error");
exit();

?>