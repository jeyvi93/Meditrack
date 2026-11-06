<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Admin-only access
if (($_SESSION['role'] ?? '') !== 'Administrator') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit();
}

// POST-only request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// CSRF validation
$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token. Please refresh the page.'
    ]);
    exit();
}

require_once "../config/database.php";

// Validate account ID
$accountId = filter_input(INPUT_POST, 'account_id', FILTER_VALIDATE_INT);

if (!$accountId || $accountId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid account ID.'
    ]);
    exit();
}

try {

    $sql = "
        DELETE FROM users
        WHERE id = ?
        AND email_verified = 0
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $accountId);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 1) {

        echo json_encode([
            'success' => true,
            'message' => 'Unverified account deleted successfully.'
        ]);

    } else {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Account not found or it is already verified.'
        ]);
    }

    mysqli_stmt_close($stmt);

} catch (mysqli_sql_exception $e) {

    error_log("Account deletion error: " . $e->getMessage());

    if ((int) $e->getCode() === 1451) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'This account has related records and cannot be deleted.'
        ]);
    } else {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Something went wrong while deleting the account.'
        ]);
    }
}