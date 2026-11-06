<?php
date_default_timezone_set('Asia/Manila');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";
require_once "../includes/activity_logger.php";

$role = $_SESSION['role'] ?? '';

$patientRoles = [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff'
];

if (!in_array($role, $patientRoles, true)) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../patient/view_appointments.php");
    exit();
}

/* Validate CSRF token */
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';


if (
    empty($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    $_SESSION['error'] = "Invalid request. Please try again.";
    header("Location: ../patient/view_appointments.php");
    exit();
}

/* Get submitted data */
$user_id = (int) $_SESSION['user_id'];
$appointment_id = (int) ($_POST['appointment_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (
    $appointment_id <= 0 ||
    $action !== 'accept'
) {
    $_SESSION['error'] = "Invalid reschedule request.";
    header("Location: ../patient/view_appointments.php");
    exit();
}


/* Get appointment and patient details */
$sql = "
    SELECT
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.proposed_date,
        a.proposed_time,
        a.status,
        a.reschedule_status,

        COALESCE(
            NULLIF(TRIM(CONCAT_WS(' ',
                NULLIF(TRIM(s.first_name), ''),
                NULLIF(TRIM(s.last_name), '')
            )), ''),

            NULLIF(TRIM(CONCAT_WS(' ',
                NULLIF(TRIM(st.first_name), ''),
                NULLIF(TRIM(st.last_name), '')
            )), ''),

            'Unknown patient'
        ) AS patient_name

    FROM appointments a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id

    WHERE a.id = ?
      AND a.user_id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['error'] = "Unable to process the reschedule request.";
    header("Location: ../patient/view_appointments.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $user_id);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$appointment = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (
    !$appointment ||
    $appointment['status'] !== 'Approved' ||
    $appointment['reschedule_status'] !== 'pending' ||
    empty($appointment['proposed_date']) ||
    empty($appointment['proposed_time'])
) {
    $_SESSION['error'] = "No pending reschedule request was found.";
    header("Location: ../patient/view_appointments.php");
    exit();
}

/* Validate proposed schedule */
$proposedDate = $appointment['proposed_date'];
$proposedTime = $appointment['proposed_time'];

$allowed_times = [
    '08:30:00',
    '10:00:00',
    '13:30:00',
    '15:00:00'
];

if (
    strtotime($proposedDate) < strtotime(date('Y-m-d')) ||
    !in_array($proposedTime, $allowed_times, true) ||
    (
        $proposedDate === date('Y-m-d') &&
        $proposedTime <= date('H:i:s')
    )
) {
    $_SESSION['error'] = "The proposed schedule is no longer valid. Please contact the clinic for another schedule.";
    header("Location: ../patient/view_appointments.php");
    exit();
}

/* Check for another appointment on the proposed date */
$duplicateSql = "
    SELECT id
    FROM appointments
    WHERE user_id = ?
      AND appointment_date = ?
      AND status IN ('Pending', 'Approved')
      AND id <> ?
    LIMIT 1
";

$duplicateStmt = mysqli_prepare($conn, $duplicateSql);

mysqli_stmt_bind_param(
    $duplicateStmt,
    "isi",
    $user_id,
    $proposedDate,
    $appointment_id
);

mysqli_stmt_execute($duplicateStmt);
$duplicateResult = mysqli_stmt_get_result($duplicateStmt);
$hasDuplicate = mysqli_num_rows($duplicateResult) > 0;

mysqli_stmt_close($duplicateStmt);

if ($hasDuplicate) {
    $_SESSION['error'] = "You already have another active appointment on the proposed date.";
    header("Location: ../patient/view_appointments.php");
    exit();
}

/* Check proposed time slot capacity */
$capacitySql = "
    SELECT COUNT(*) AS total
    FROM appointments
    WHERE appointment_date = ?
      AND appointment_time = ?
      AND status IN ('Pending', 'Approved')
      AND id <> ?
";

$capacityStmt = mysqli_prepare($conn, $capacitySql);

mysqli_stmt_bind_param(
    $capacityStmt,
    "ssi",
    $proposedDate,
    $proposedTime,
    $appointment_id
);

mysqli_stmt_execute($capacityStmt);
$capacityResult = mysqli_stmt_get_result($capacityStmt);
$capacity = mysqli_fetch_assoc($capacityResult);

mysqli_stmt_close($capacityStmt);

if ((int) $capacity['total'] >= 2) {
    $_SESSION['error'] = "The proposed time slot is already full. Please contact the clinic for another schedule.";
    header("Location: ../patient/view_appointments.php");
    exit();
}

/* Accept proposed schedule */
$updateSql = "
    UPDATE appointments
    SET
        appointment_date = proposed_date,
        appointment_time = proposed_time,
        proposed_date = NULL,
        proposed_time = NULL,
        reschedule_status = NULL,
        status = 'Approved'
    WHERE id = ?
      AND user_id = ?
      AND status = 'Approved'
      AND reschedule_status = 'pending'
      AND proposed_date = ?
      AND proposed_time = ?
";


$updateStmt = mysqli_prepare($conn, $updateSql);


if (!$updateStmt) {
    $_SESSION['error'] = "Unable to accept the proposed schedule.";
    header("Location: ../patient/view_appointments.php");
    exit();
}

mysqli_stmt_bind_param(
    $updateStmt,
    "iiss",
    $appointment_id,
    $user_id,
    $proposedDate,
    $proposedTime
);

if (
    mysqli_stmt_execute($updateStmt) &&
    mysqli_stmt_affected_rows($updateStmt) === 1
) {
    $patientName = $appointment['patient_name'];

    $formattedDate = date("M d, Y", strtotime($proposedDate));
    $formattedTime = date("h:i A", strtotime($proposedTime));

    $description = "$patientName accepted the rescheduled appointment for $formattedDate at $formattedTime.";

    logActivity(
        $conn,
        $user_id,
        'Appointment',
        'Reschedule Accepted',
        $description
    );

    $_SESSION['success'] = "Schedule Accepted!";
    $_SESSION['success_message'] = "Your appointment has been updated to the proposed schedule.";
} else {
    $_SESSION['error'] = "Unable to accept the proposed schedule. Please try again.";
}

mysqli_stmt_close($updateStmt);

header("Location: ../patient/view_appointments.php");
exit();