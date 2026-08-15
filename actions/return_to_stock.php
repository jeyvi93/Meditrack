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
// VALIDATE MEDICINE ID
// ==========================

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid medicine ID."
    ]);

    exit();
}

$id = (int) $_POST['id'];


// ==========================
// CHECK MEDICINE
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, is_quarantined, status
     FROM medicines
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$medicine = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$medicine) {

    echo json_encode([
        "success" => false,
        "message" => "Medicine not found."
    ]);

    exit();
}


// ==========================
// CHECK QUARANTINE STATUS
// ==========================

if ((int)$medicine['is_quarantined'] !== 1) {

    echo json_encode([
        "success" => false,
        "message" => "This medicine is not currently in quarantine."
    ]);

    exit();
}


// ==========================
// RETURN TO STOCK
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "UPDATE medicines
     SET is_quarantined = 0,
         status = 'Active'
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);


if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "success" => true,
        "message" => "Medicine returned to stock successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to return medicine to stock."
    ]);
}


mysqli_stmt_close($stmt);
mysqli_close($conn);