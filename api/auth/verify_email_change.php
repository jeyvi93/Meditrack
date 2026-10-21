<?php

require_once "../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}

$token = $_GET["token"] ?? "";

if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}


// ==========================
// FIND PENDING EMAIL CHANGE
// ==========================

$sql = "
    SELECT id, pending_email, email_change_expires
    FROM users
    WHERE email_change_token = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user || empty($user["pending_email"])) {
    mysqli_close($conn);
    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}


// ==========================
// CHECK EXPIRATION
// ==========================

if (
    empty($user["email_change_expires"]) ||
    strtotime($user["email_change_expires"]) < time()
) {
    // Clear expired request so it cannot be reused.
    $clearStmt = mysqli_prepare(
        $conn,
        "UPDATE users
         SET pending_email = NULL,
             email_change_token = NULL,
             email_change_expires = NULL
         WHERE id = ? AND email_change_token = ?"
    );

    if ($clearStmt) {
        mysqli_stmt_bind_param($clearStmt, "is", $user["id"], $token);
        mysqli_stmt_execute($clearStmt);
        mysqli_stmt_close($clearStmt);
    }

    mysqli_close($conn);

    header("Location: ../../patient/verification_result.php?status=expired");
    exit();
}


// ==========================
// RECHECK DUPLICATE EMAIL
// ==========================

$duplicateStmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM users
     WHERE email = ? AND id != ?
     LIMIT 1"
);

if (!$duplicateStmt) {
    mysqli_close($conn);
    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}

mysqli_stmt_bind_param(
    $duplicateStmt,
    "si",
    $user["pending_email"],
    $user["id"]
);

mysqli_stmt_execute($duplicateStmt);

$duplicateResult = mysqli_stmt_get_result($duplicateStmt);
$duplicateExists = mysqli_num_rows($duplicateResult) > 0;

mysqli_stmt_close($duplicateStmt);

if ($duplicateExists) {
    // The address is now in use; discard this pending request.
    $clearStmt = mysqli_prepare(
        $conn,
        "UPDATE users
         SET pending_email = NULL,
             email_change_token = NULL,
             email_change_expires = NULL
         WHERE id = ? AND email_change_token = ?"
    );

    if ($clearStmt) {
        mysqli_stmt_bind_param($clearStmt, "is", $user["id"], $token);
        mysqli_stmt_execute($clearStmt);
        mysqli_stmt_close($clearStmt);
    }

    mysqli_close($conn);

    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}


// ==========================
// APPLY EMAIL CHANGE
// ==========================

// Conditional update prevents reusing an expired or replaced token.
$updateStmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET email = pending_email,
         pending_email = NULL,
         email_change_token = NULL,
         email_change_expires = NULL
     WHERE id = ?
       AND email_change_token = ?
       AND email_change_expires >= NOW()
       AND pending_email IS NOT NULL"
);


if (!$updateStmt) {
    mysqli_close($conn);
    header("Location: ../../patient/verification_result.php?status=error");
    exit();
}

mysqli_stmt_bind_param($updateStmt, "is", $user["id"], $token);


$executed = mysqli_stmt_execute($updateStmt);
$affectedRows = mysqli_stmt_affected_rows($updateStmt);
$sqlError = mysqli_stmt_error($updateStmt);

error_log(
    "MediTrack email verification: executed=" .
    (int)$executed .
    ", affected_rows=" . $affectedRows .
    ", SQL error=" . $sqlError
);


error_log(
    "MediTrack email verification: executed=" .
    (int)$executed .
    ", affected_rows=" . $affectedRows .
    ", SQL error=" . $sqlError
);

$success = $executed && $affectedRows === 1;

mysqli_stmt_close($updateStmt);
mysqli_close($conn);

if ($success) {
    header("Location: ../../patient/verification_result.php?status=email_changed");
    exit();
}

header("Location: ../../patient/verification_result.php?status=error");
exit();

?>
