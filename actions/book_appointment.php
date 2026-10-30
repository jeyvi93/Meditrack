<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/database.php";

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? '';

if (($_SESSION['role'] ?? '') === 'Administrator') {
    header("Location: ../admin/accounts.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'Clinic Staff') {
    header("Location: ../clinic/dashboard.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'School Nurse') {
    header("Location: ../clinic/dashboard.php");
    exit();
}
// ==========================================
// ONLY ALLOW POST REQUEST
// ==========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../patient/book_appointment.php");
    exit();
}


// ==========================================
// GET FORM DATA
// ==========================================

$purpose = trim($_POST['purpose'] ?? '');
$note = trim($_POST['note'] ?? '');
$appointment_date = trim($_POST['appointment_date'] ?? '');
$appointment_time = trim($_POST['appointment_time'] ?? '');


// ==========================================
// VALIDATE REQUIRED FIELDS
// ==========================================

if ($purpose === '') {
    $_SESSION['error'] = "Please enter the purpose of your appointment.";
    header("Location: ../patient/book_appointment.php");
    exit();
}

if ($appointment_date === '') {
    $_SESSION['error'] = "Please select an appointment date.";
    header("Location: ../patient/book_appointment.php");
    exit();
}

if ($appointment_time === '') {
    $_SESSION['error'] = "Please select an appointment time.";
    header("Location: ../patient/book_appointment.php");
    exit();
}


// ==========================================
// VALIDATE DATE FORMAT
// ==========================================

$dateObject = DateTime::createFromFormat('Y-m-d', $appointment_date);

if (!$dateObject || $dateObject->format('Y-m-d') !== $appointment_date) {
    $_SESSION['error'] = "Invalid appointment date.";
    header("Location: ../patient/book_appointment.php");
    exit();
}


// ==========================================
// PREVENT PAST DATES
// ==========================================

$today = date('Y-m-d');

if ($appointment_date < $today) {
    $_SESSION['error'] = "Appointment date cannot be in the past.";
    header("Location: ../patient/book_appointment.php");
    exit();
}


// ==========================================
// VALIDATE AVAILABLE TIME
// ==========================================

$allowed_times = [
    '08:30:00',
    '10:00:00',
    '13:30:00',
    '15:00:00'
];

if (!in_array($appointment_time, $allowed_times, true)) {
    $_SESSION['error'] = "Invalid appointment time selected.";
    header("Location: ../patient/book_appointment.php");
    exit();
}


// ==========================================
// CHECK IF USER EXISTS
// ==========================================

$userCheck = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE id = ? LIMIT 1"
);

if (!$userCheck) {
    $_SESSION['error'] = "Unable to process your appointment request.";
    mysqli_close($conn);
    header("Location: ../patient/book_appointment.php");
    exit();
}

mysqli_stmt_bind_param(
    $userCheck,
    "i",
    $user_id
);

mysqli_stmt_execute($userCheck);
mysqli_stmt_store_result($userCheck);

if (mysqli_stmt_num_rows($userCheck) === 0) {

    mysqli_stmt_close($userCheck);
    mysqli_close($conn);

    $_SESSION['error'] = "Your account could not be verified.";
    header("Location: ../patient/book_appointment.php");
    exit();
}

mysqli_stmt_close($userCheck);


// ==========================================
// PREVENT MORE THAN ONE APPOINTMENT PER DAY
// ==========================================

$duplicateCheck = mysqli_prepare(
    $conn,
    "SELECT id
     FROM appointments
     WHERE user_id = ?
     AND appointment_date = ?
     AND status IN ('Pending', 'Approved')
     LIMIT 1"
);

if (!$duplicateCheck) {
    $_SESSION['error'] = "Unable to check your existing appointments.";
    mysqli_close($conn);
    header("Location: ../patient/book_appointment.php");
    exit();
}

mysqli_stmt_bind_param(
    $duplicateCheck,
    "is",
    $user_id,
    $appointment_date
);

mysqli_stmt_execute($duplicateCheck);
mysqli_stmt_store_result($duplicateCheck);

if (mysqli_stmt_num_rows($duplicateCheck) > 0) {

    mysqli_stmt_close($duplicateCheck);
    mysqli_close($conn);

    $_SESSION['error'] = "You already have an appointment for this date.";
    header("Location: ../patient/book_appointment.php");
    exit();
}

mysqli_stmt_close($duplicateCheck);


// ==========================================
// LIMIT EACH TIME SLOT TO 2 APPOINTMENTS
// ==========================================

$slotCheck = mysqli_prepare(
    $conn,
    "SELECT COUNT(*)
     FROM appointments
     WHERE appointment_date = ?
     AND appointment_time = ?
     AND status IN ('Pending', 'Approved')"
);

if (!$slotCheck) {
    $_SESSION['error'] = "Unable to check appointment availability.";
    mysqli_close($conn);
    header("Location: ../patient/book_appointment.php");
    exit();
}

mysqli_stmt_bind_param(
    $slotCheck,
    "ss",
    $appointment_date,
    $appointment_time
);

mysqli_stmt_execute($slotCheck);

mysqli_stmt_bind_result(
    $slotCheck,
    $slotCount
);

mysqli_stmt_fetch($slotCheck);
mysqli_stmt_close($slotCheck);

if ($slotCount >= 2) {

    mysqli_close($conn);

    $_SESSION['error'] = "This appointment time is already full. Please select another time.";
    header("Location: ../patient/book_appointment.php");
    exit();
}


// ==========================================
// CREATE APPOINTMENT
// ==========================================

$status = "Pending";

$sql = "
    INSERT INTO appointments (
        user_id,
        appointment_date,
        appointment_time,
        purpose,
        status,
        note
    )
    VALUES (?, ?, ?, ?, ?, ?)
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    $_SESSION['error'] = "Unable to create your appointment request.";

    mysqli_close($conn);

    header("Location: ../patient/book_appointment.php");
    exit();
}


// ==========================================
// BIND PARAMETERS
// ==========================================

mysqli_stmt_bind_param(
    $stmt,
    "isssss",
    $user_id,
    $appointment_date,
    $appointment_time,
    $purpose,
    $status,
    $note
);


// ==========================================
// EXECUTE INSERT
// ==========================================

if (mysqli_stmt_execute($stmt)) {

    // ==========================================
    // LOG APPOINTMENT ACTIVITY
    // ==========================================

    $activitySql = "
        INSERT INTO activity_logs
        (user_id, activity_type, action, description)
        VALUES (?, 'Appointment', 'New', ?)
    ";

    $description = $full_name . " made an appointment.";

    $activityStmt = mysqli_prepare($conn, $activitySql);

    if ($activityStmt) {

        mysqli_stmt_bind_param(
            $activityStmt,
            "is",
            $user_id,
            $description
        );

        mysqli_stmt_execute($activityStmt);
        mysqli_stmt_close($activityStmt);
    }

    $_SESSION['success'] = "Appointment request submitted successfully.";

} else {

    $_SESSION['error'] = "Failed to submit appointment request.";
}

// ==========================================
// CLOSE CONNECTION
// ==========================================

mysqli_stmt_close($stmt);
mysqli_close($conn);


// ==========================================
// RETURN TO BOOKING PAGE
// ==========================================

header("Location: ../patient/book_appointment.php");
exit();

?>