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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid medicine ID"
    ]);
    exit();
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT 
        id,
        medicine_name,
        batch_number,
        quantity,
        supplier,
        date_acquired,
        expiry_date
     FROM medicines
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {

    echo json_encode([
        "success" => true,
        "id" => $row["id"],
        "medicine_name" => $row["medicine_name"],
        "batch_number" => $row["batch_number"],
        "quantity" => $row["quantity"],
        "supplier" => $row["supplier"],
        "date_acquired" => $row["date_acquired"],
        "expiry_date" => $row["expiry_date"]
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Medicine not found"
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);