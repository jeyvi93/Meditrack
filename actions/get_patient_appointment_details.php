<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit();
}

$allowedRoles = [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff'
];

if (!in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit();
}

require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];
$appointment_id = (int) ($_GET['appointment_id'] ?? 0);

if ($appointment_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid appointment.'
    ]);
    exit();
}

$sql = "
    SELECT
        c.id,
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
    FROM appointments a
    INNER JOIN consultations c
        ON c.appointment_id = a.id
    LEFT JOIN consultation_medicine cm
        ON cm.consultation_id = c.id
    LEFT JOIN medicines m
        ON m.id = cm.medicine_id
    WHERE
        a.id = ?
        AND a.user_id = ?
        AND LOWER(a.status) = 'completed'
    GROUP BY c.id
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$consultation = $result->fetch_assoc();

$stmt->close();

if (!$consultation) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'No consultation record found for this appointment.'
    ]);
    exit();
}

echo json_encode([
    'success' => true,
    'consultation' => $consultation
]);
