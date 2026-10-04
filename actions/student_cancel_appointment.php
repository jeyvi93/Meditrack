<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/* Make sure request is POST */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../patient/view_appointments.php");
    exit();
}


/* Get submitted data */
$appointment_id = (int) ($_POST['appointment_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');


/* Validate appointment ID */
if ($appointment_id <= 0) {

    $_SESSION['error'] = "Invalid appointment.";

    header("Location: ../patient/view_appointments.php");
    exit();
}


/* Validate cancellation reason */
if ($reason === '') {

    $_SESSION['error'] = "Please provide a reason for cancellation.";

    header("Location: ../patient/view_appointments.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get appointment
|--------------------------------------------------------------------------
| Make sure the appointment belongs to the logged-in student.
*/

$sql = "
    SELECT
        a.id,
        a.user_id,
        a.status,
        a.appointment_date,
        a.appointment_time,
        a.purpose,
        u.full_name
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ?
      AND a.user_id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    $_SESSION['error'] = "Something went wrong.";

    header("Location: ../patient/view_appointments.php");
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $appointment_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$appointment = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* Appointment not found */
if (!$appointment) {

    $_SESSION['error'] =
        "Appointment not found or you are not allowed to cancel it.";

    header("Location: ../patient/view_appointments.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Check appointment status
|--------------------------------------------------------------------------
| Students can only cancel Pending or Approved appointments.
*/

$status = strtolower(trim($appointment['status']));

if ($status !== 'pending' && $status !== 'approved') {

    $_SESSION['error'] =
        "This appointment can no longer be cancelled.";

    header("Location: ../patient/view_appointments.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Cancel appointment
|--------------------------------------------------------------------------
*/

$updateSql = "
    UPDATE appointments
    SET
        status = 'Cancelled',
        reason = ?
    WHERE id = ?
      AND user_id = ?
";

$updateStmt = mysqli_prepare($conn, $updateSql);

if (!$updateStmt) {

    $_SESSION['error'] =
        "Failed to process the cancellation.";

    header("Location: ../patient/view_appointments.php");
    exit();
}

mysqli_stmt_bind_param(
    $updateStmt,
    "sii",
    $reason,
    $appointment_id,
    $user_id
);

$success = mysqli_stmt_execute($updateStmt);

$affectedRows = mysqli_stmt_affected_rows($updateStmt);

mysqli_stmt_close($updateStmt);


/*
|--------------------------------------------------------------------------
| Activity Logger
|--------------------------------------------------------------------------
*/

if ($success && $affectedRows > 0) {

    $activitySql = "
        INSERT INTO activity_logs
        (user_id, activity_type, action, description)
        VALUES (?, 'Appointment', 'Cancelled', ?)
    ";

    $description =
        $appointment['full_name'] .
        " cancelled their appointment.\n" .
        "Reason: " .
        $reason;

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

    $_SESSION['success'] =
        "Appointment cancelled successfully.";

} else {

    $_SESSION['error'] =
        "Failed to cancel appointment.";
}


mysqli_close($conn);

header("Location: ../patient/view_appointments.php");
exit();

?>