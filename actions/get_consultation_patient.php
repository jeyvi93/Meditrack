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

$idNumber = trim($_GET['id_number'] ?? '');

if ($idNumber === '') {
    echo json_encode([
        "success" => false,
        "message" => "Please enter an ID number."
    ]);
    exit();
}

// Search students first
$stmt = $conn->prepare("
    SELECT
        student_id_number AS id_number,
        first_name,
        middle_name,
        last_name,
        date_of_birth,
        sex,
        contact_number,
        department,
        course,
        year_level
    FROM students
    WHERE student_id_number = ?
    AND is_archived = 0
    LIMIT 1
");

$stmt->bind_param("s", $idNumber);
$stmt->execute();
$result = $stmt->get_result();

$patient = $result->fetch_assoc();
$stmt->close();

if ($patient) {
    $patient['patient_type'] = 'student';
} else {

    // Search teaching and non-teaching staff
    $stmt = $conn->prepare("
        SELECT
            staff_id_number AS id_number,
            first_name,
            middle_name,
            last_name,
            date_of_birth,
            sex,
            contact_number,
            department,
            position
        FROM staffs
        WHERE staff_id_number = ?
        AND is_archived = 0
        LIMIT 1
    ");

    $stmt->bind_param("s", $idNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    $patient = $result->fetch_assoc();
    $stmt->close();

    if ($patient) {
        $position = strtolower(trim($patient['position']));

        if ($position === 'teaching staff') {
            $patient['patient_type'] = 'tstaff';
        } elseif ($position === 'non-teaching staff') {
            $patient['patient_type'] = 'nstaff';
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Invalid staff position. Please check the staff profile."
            ]);
            exit();
        }
    }
}

if (!$patient) {
    echo json_encode([
        "success" => false,
        "message" => "No active student or staff record found with that ID number."
    ]);
    exit();
}

// Calculate age
$birthDate = new DateTime($patient['date_of_birth']);
$today = new DateTime();
$patient['age'] = $birthDate->diff($today)->y;

echo json_encode([
    "success" => true,
    "patient" => $patient
]);
