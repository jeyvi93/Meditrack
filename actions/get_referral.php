<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "You are not authorized to access referral information."
    ]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Referral ID is missing"
    ]);
    exit();
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM referrals WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Referral not found"
    ]);
    exit();
}

$referral = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "referral" => $referral
]);