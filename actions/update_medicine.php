<?php

session_start();
require_once "../config/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit();
}

$id = (int)($_POST['id'] ?? 0);
$medicine_name = $_POST['medicine_name'] ?? '';
$batch_number = $_POST['batch_number'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 0);
$supplier = $_POST['supplier'] ?? '';
$date_acquired = $_POST['date_acquired'] ?? '';
$expiry_date = $_POST['expiry_date'] ?? '';

if (
    !$id ||
    !$medicine_name ||
    !$batch_number ||
    !isset($_POST['quantity']) ||
    !$supplier ||
    !$date_acquired ||
    !$expiry_date
) {
    echo json_encode([
        "success" => false,
        "message" => "Please complete all fields."
    ]);
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE medicines
     SET medicine_name = ?,
         batch_number = ?,
         quantity = ?,
         supplier = ?,
         date_acquired = ?,
         expiry_date = ?
     WHERE id = ?"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => mysqli_error($conn)
    ]);
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "ssisssi",
    $medicine_name,
    $batch_number,
    $quantity,
    $supplier,
    $date_acquired,
    $expiry_date,
    $id
);

if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "success" => true,
        "message" => "Medicine updated successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);