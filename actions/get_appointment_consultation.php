<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit();
}

require_once "../config/database.php";

$role = $_SESSION['role'] ?? '';

if (in_array($role, [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff',
    'Administrator'
], true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit();
}

$appointment_id = (int) ($_GET['appointment_id'] ?? 0);

if ($appointment_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid appointment.'
    ]);
    exit();
}

$stmt = $conn->prepare("
    SELECT
        a.id,
        a.purpose,
        a.status,
        s.student_id_number,
        st.staff_id_number
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    WHERE a.id = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve appointment.'
    ]);
    exit();
}

$stmt->bind_param("i", $appointment_id);
$stmt->execute();

$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

$stmt->close();
$conn->close();

if (!$appointment) {
    echo json_encode([
        'success' => false,
        'message' => 'Appointment not found.'
    ]);
    exit();
}

if ($appointment['status'] !== 'Approved') {
    echo json_encode([
        'success' => false,
        'message' => 'Only approved appointments can be processed.'
    ]);
    exit();
}

$id_number = $appointment['student_id_number']
    ?: $appointment['staff_id_number'];

if (!$id_number) {
    echo json_encode([
        'success' => false,
        'message' => 'Patient profile could not be identified.'
    ]);
    exit();
}

echo json_encode([
    'success' => true,
    'appointment_id' => (int) $appointment['id'],
    'purpose' => $appointment['purpose'],
    'id_number' => $id_number
]);
