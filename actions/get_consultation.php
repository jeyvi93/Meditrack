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
// GET CONSULTATION ID
// ==========================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid consultation ID."
    ]);

    exit();
}

$consultationId = (int) $_GET['id'];


// ==========================
// GET CONSULTATION
// ==========================

$sql = "
    SELECT
        id,
        date,
        time,
        patient_type,
        full_name,
        age,
        sex,
        contact_number,
        department,
        course,
        year_level,
        position,
        vital_signs,
        complaint,
        treatment_given,
        remarks
    FROM consultations
    WHERE id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $consultationId);

$stmt->execute();

$result = $stmt->get_result();


// ==========================
// CHECK CONSULTATION
// ==========================

if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Consultation record not found."
    ]);

    exit();
}

$consultation = $result->fetch_assoc();


// ==========================
// GET MEDICINES DISPENSED
// ==========================

$medicineSql = "
    SELECT
        m.medicine_name,
        cm.quantity_used AS quantity
    FROM consultation_medicine cm
    INNER JOIN medicines m
        ON cm.medicine_id = m.id
    WHERE cm.consultation_id = ?
";

$medicineStmt = $conn->prepare($medicineSql);

if (!$medicineStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Medicine query preparation failed: " . $conn->error
    ]);
    exit();
}

$medicineStmt->bind_param("i", $consultationId);

$medicineStmt->execute();

$medicineResult = $medicineStmt->get_result();

$medicines = [];

while ($medicine = $medicineResult->fetch_assoc()) {
    $medicines[] = $medicine;
}


// ==========================
// RETURN DATA
// ==========================

echo json_encode([
    "success" => true,
    "consultation" => $consultation,
    "medicines" => $medicines
]);