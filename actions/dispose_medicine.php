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
        "message" => "Unauthorized"
    ]);

    exit();
}


// ==========================
// REQUEST METHOD
// ==========================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);

    exit();
}


// ==========================
// GET POST DATA
// ==========================

$id = $_POST['id'] ?? null;
$disposal_reason = $_POST['disposal_reason'] ?? '';

$disposed_by = $_SESSION['user_id'];


// ==========================
// VALIDATION
// ==========================

if (!$id || !$disposal_reason) {

    echo json_encode([
        "success" => false,
        "message" => "Missing disposal information."
    ]);

    exit();
}


// ==========================
// GET MEDICINE
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM medicines
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$medicine = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// ==========================
// CHECK MEDICINE
// ==========================

if (!$medicine) {

    echo json_encode([
        "success" => false,
        "message" => "Medicine not found."
    ]);

    exit();
}


// ==========================
// CHECK IF ALREADY DISPOSED
// ==========================

if ($medicine['status'] === 'Disposed') {

    echo json_encode([
        "success" => false,
        "message" => "This medicine has already been disposed."
    ]);

    exit();
}


// ==========================
// INSERT DISPOSAL RECORD
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO disposed_medicines
    (
        medicine_id,
        medicine_name,
        batch_number,
        quantity_disposed,
        disposal_reason,
        disposed_by
    )
    VALUES (?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "issisi",
    $id,
    $medicine['medicine_name'],
    $medicine['batch_number'],
    $medicine['quantity'],
    $disposal_reason,
    $disposed_by
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => false,
        "message" => "Failed to create disposal record."
    ]);

    exit();
}

mysqli_stmt_close($stmt);


// ==========================
// MARK MEDICINE AS DISPOSED
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "UPDATE medicines
     SET status = 'Disposed',
         is_quarantined = 0
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => false,
        "message" => "Disposal record created, but medicine status could not be updated."
    ]);

    exit();
}

mysqli_stmt_close($stmt);

mysqli_close($conn);


// ==========================
// SUCCESS
// ==========================

echo json_encode([
    "success" => true,
    "message" => "Medicine disposed successfully."
]);