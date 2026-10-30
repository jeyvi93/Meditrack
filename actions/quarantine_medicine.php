<?php

session_start();
require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    echo json_encode([
        "success" => false,
        "message" => "You are not authorized to quarantine medicine."
    ]);
    exit();
}

$role = $_SESSION['role'] ?? '';

if (in_array($role, [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff'
], true)) {

    header("Location: ../patient/view_consultation.php");
    exit();

}

if ($role === 'Administrator') {

    header("Location: ../admin/accounts.php");
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

// ==========================
// GET MEDICINE
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "SELECT medicine_name, batch_number
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

$stmt = mysqli_prepare(
    $conn,
    "UPDATE medicines
     SET is_quarantined = 1
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    /* Clinic Activity Log */
    logClinicActivity(
        $conn,
        'Quarantined',
        'Medicine',
        "Moved {$medicine['medicine_name']} to quarantine. Batch: {$medicine['batch_number']}."
    );

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