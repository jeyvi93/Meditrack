<?php
session_start();

require_once "../config/database.php";

// Allow School Nurse only
if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'School Nurse'
) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);

    exit();
}

$staff_id = isset($_GET['staff_id'])
    ? (int) $_GET['staff_id']
    : 0;

if ($staff_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid staff ID."
    ]);

    exit();
}

$sql = "
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
        c.staff_id = ?
        OR (
            c.staff_id IS NULL
            AND u.staff_id = ?
        )

    GROUP BY c.id

    ORDER BY c.date DESC, c.time DESC
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to retrieve medical history."
    ]);

    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $staff_id,
    $staff_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$consultations = [];

while ($row = mysqli_fetch_assoc($result)) {
    $consultations[] = $row;
}

echo json_encode([
    "success" => true,
    "consultations" => $consultations
]);

mysqli_stmt_close($stmt);
$conn->close();
