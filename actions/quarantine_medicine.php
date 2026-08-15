<?php

session_start();
require_once "../config/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid medicine ID."
    ]);
    exit();
}

$id = (int) $_POST['id'];

$stmt = mysqli_prepare(
    $conn,
    "UPDATE medicines
     SET is_quarantined = 1
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "success" => true,
        "message" => "Medicine moved to quarantine."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to quarantine medicine."
    ]);
}

mysqli_stmt_close($stmt);