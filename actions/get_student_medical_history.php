<?php

session_start();

require_once "../config/database.php";

header("Content-Type: application/json");

// ==========================
// AUTHENTICATION
// ==========================

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);
    exit();
}

if (($_SESSION['role'] ?? '') !== 'School Nurse') {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Access denied."
    ]);
    exit();
}

// ==========================
// VALIDATE STUDENT ID
// ==========================

$student_id = (int) ($_GET['student_id'] ?? 0);

if ($student_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid student ID."
    ]);
    exit();
}

try {

    // ==========================
    // CONSULTATION HISTORY
    // ==========================

    $consultationStmt = $conn->prepare("
        SELECT
            c.id,
            c.appointment_id,
            c.appointment_mode,
            c.appointment_reason,
            c.date,
            c.time,
            c.complaint,
            c.treatment_given,
            c.remarks,
            GROUP_CONCAT(
                CONCAT(m.medicine_name, ' (', cm.quantity_used, ')')
                ORDER BY m.medicine_name
                SEPARATOR ', '
            ) AS medicines_dispensed
        FROM consultations c
        LEFT JOIN appointments a
            ON c.appointment_id = a.id
        LEFT JOIN users u
            ON a.user_id = u.id
        LEFT JOIN consultation_medicine cm
            ON c.id = cm.consultation_id
        LEFT JOIN medicines m
            ON cm.medicine_id = m.id
        WHERE
            c.student_id = ?
            OR (
                c.student_id IS NULL
                AND u.student_id = ?
            )
        GROUP BY c.id
        ORDER BY c.date DESC, c.time DESC
    ");

    if (!$consultationStmt) {
        throw new Exception("Unable to retrieve consultation history.");
    }

    $consultationStmt->bind_param(
        "ii",
        $student_id,
        $student_id
    );

    $consultationStmt->execute();

    $consultationResult = $consultationStmt->get_result();

    $consultations = [];

    while ($row = $consultationResult->fetch_assoc()) {
        $consultations[] = $row;
    }

    $consultationStmt->close();

    // ==========================
    // RESPONSE
    // ==========================

    echo json_encode([
        "success" => true,
        "consultations" => $consultations
    ]);

} catch (Throwable $e) {

    error_log("Medical history retrieval error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load medical history."
    ]);
}

$conn->close();
