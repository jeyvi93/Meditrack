<?php

session_start();
require_once "../config/database.php";

header("Content-Type: application/json");


// ==========================
// AUTHENTICATION
// ==========================

if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);

    exit();
}

// ==========================
// ROLE AUTHORIZATION
// ==========================

if ($_SESSION['role'] === 'Clinic Staff') {

    echo json_encode([
        "success" => false,
        "message" => "You are not authorized to add medicine."
    ]);

    exit();
}

// ==========================
// REQUEST METHOD
// ==========================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit();
}


// ==========================
// GET POST DATA
// ==========================

$medicine_name = trim($_POST['medicine_name'] ?? '');
$batch_number = trim($_POST['batch_number'] ?? '');
$supplier = trim($_POST['supplier'] ?? '');

$quantity = $_POST['quantity'] ?? null;
$low_stock_threshold = $_POST['low_stock_threshold'] ?? null;

$date_acquired = $_POST['date_acquired'] ?? '';
$expiry_date = $_POST['expiry_date'] ?? '';


// ==========================
// REQUIRED FIELD VALIDATION
// ==========================

if (
    !$medicine_name ||
    !$batch_number ||
    !$supplier ||
    $quantity === null ||
    $low_stock_threshold === null ||
    !$date_acquired ||
    !$expiry_date
) {

    echo json_encode([
        "success" => false,
        "message" => "Please complete all required fields."
    ]);

    exit();
}


// ==========================
// NUMERIC VALIDATION
// ==========================

if (
    !is_numeric($quantity) ||
    !is_numeric($low_stock_threshold)
) {

    echo json_encode([
        "success" => false,
        "message" => "Quantity and low stock threshold must be numbers."
    ]);

    exit();
}

$quantity = (int) $quantity;
$low_stock_threshold = (int) $low_stock_threshold;


// ==========================
// QUANTITY VALIDATION
// ==========================

if ($quantity < 1) {

    echo json_encode([
        "success" => false,
        "message" => "Quantity must be at least 1."
    ]);

    exit();
}

if ($low_stock_threshold < 1) {

    echo json_encode([
        "success" => false,
        "message" => "Low stock threshold must be at least 1."
    ]);

    exit();
}


// ==========================
// DATE VALIDATION
// ==========================

if (strtotime($expiry_date) < strtotime($date_acquired)) {

    echo json_encode([
        "success" => false,
        "message" => "Expiration date cannot be earlier than the date acquired."
    ]);

    exit();
}


// ==========================
// CHECK DUPLICATE BATCH
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM medicines
     WHERE medicine_name = ?
     AND batch_number = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $medicine_name,
    $batch_number
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$existing_batch = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if ($existing_batch) {

    echo json_encode([
        "success" => false,
        "message" => "This batch number already exists for this medicine."
    ]);

    exit();
}


// ==========================
// INSERT MEDICINE
// ==========================

$status = "Active";
$is_quarantined = 0;

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO medicines
    (
        medicine_name,
        batch_number,
        quantity,
        low_stock_threshold,
        date_acquired,
        expiry_date,
        supplier,
        status,
        is_quarantined
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssiissssi",
    $medicine_name,
    $batch_number,
    $quantity,
    $low_stock_threshold,
    $date_acquired,
    $expiry_date,
    $supplier,
    $status,
    $is_quarantined
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => false,
        "message" => "Failed to add medicine to inventory."
    ]);

    exit();
}

mysqli_stmt_close($stmt);


// ==========================
// ACTIVITY LOG
// ==========================

$activity_type = "Medicine";
$action = "Added";
$description = "Added {$medicine_name} to inventory. Batch: {$batch_number}, Quantity: {$quantity}.";

$logStmt = mysqli_prepare(
    $conn,
    "INSERT INTO activity_logs
    (user_id, activity_type, action, description)
    VALUES (?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $logStmt,
    "isss",
    $_SESSION['user_id'],
    $activity_type,
    $action,
    $description
);

mysqli_stmt_execute($logStmt);
mysqli_stmt_close($logStmt);

mysqli_close($conn);


// ==========================
// SUCCESS
// ==========================

echo json_encode([
    "success" => true,
    "message" => "Medicine added successfully."
]);