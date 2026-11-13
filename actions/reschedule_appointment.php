<?php
date_default_timezone_set('Asia/Manila');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";
require_once __DIR__ . '/../config/firebase_messaging.php';
require_once "../includes/clinic_activity_logger.php";

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

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $appointment_id = $_POST['appointment_id'];
    $date = $_POST['appointment_date'];
    $timeInput = trim($_POST['appointment_time']);

    $reason = trim($_POST['reason'] ?? '');

    if ($reason === '') {
        $_SESSION['error'] = "Please provide a reason for rescheduling.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    // Allowed appointment time slots
    $allowedTimes = [
        '08:30:00',
        '10:00:00',
        '13:30:00',
        '15:00:00'
    ];

    // Validate appointment date
    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);
    $dateErrors = DateTime::getLastErrors();

    if (
        !$dateObject ||
        ($dateErrors !== false &&
            ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
        $dateObject->format('Y-m-d') !== $date
    ) {
        $_SESSION['error'] = "Invalid appointment date.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    // Prevent past dates
    if ($date < date('Y-m-d')) {
        $_SESSION['error'] = "You cannot reschedule an appointment to a past date.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    // Prevent Sunday rescheduling
    if ($dateObject->format('w') === '0') {
        $_SESSION['error'] = "Rescheduling is not available on Sundays. Please select another date.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    // Validate allowed time slots
    if (!in_array($timeInput, $allowedTimes, true)) {
        $_SESSION['error'] = "Invalid appointment time.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    $time = $timeInput;

    // Prevent past time slots today
    if ($date === date('Y-m-d') && $time <= date('H:i:s')) {
        $_SESSION['error'] = "This appointment time has already passed. Please select another time.";
        header("Location: ../clinic/appointments.php");
        exit();
    }
    
    /* Get patient name and user ID */
    $patientSql = "
        SELECT
            a.user_id,

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
    ";

    $patientStmt = mysqli_prepare($conn, $patientSql);

    mysqli_stmt_bind_param(
        $patientStmt,
        "i",
        $appointment_id
    );

    mysqli_stmt_execute($patientStmt);

    $patientResult = mysqli_stmt_get_result($patientStmt);
    $appointment = mysqli_fetch_assoc($patientResult);

    $patientId = $appointment['user_id'] ?? null;
    $patientName = $appointment['patient_name'] ?? 'Unknown patient';

    mysqli_stmt_close($patientStmt);

    $sql = "
        UPDATE appointments
        SET
            proposed_date = ?,
            proposed_time = ?,
            reschedule_reason = ?,
            reschedule_status = 'pending'
        WHERE id = ?
        AND status = 'Approved'
        AND (reschedule_status IS NULL OR reschedule_status <> 'pending')
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $date,
        $time,
        $reason,
        $appointment_id
    );

    if(mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1){

        /* Notification */
        $notificationSql = "
            INSERT INTO notifications
            (user_id, appointment_id, title, message)
            VALUES (?, ?, ?, ?)
        ";

        $title = "Appointment Reschedule Request";

        $message = "The clinic has requested to reschedule your appointment to "
                . date("F j, Y", strtotime($date))
                . " at "
                . date("g:i A", strtotime($time))
                . ". Please review the request in your appointments page.";

        $notificationStmt = mysqli_prepare($conn, $notificationSql);

        if ($notificationStmt) {

            mysqli_stmt_bind_param(
                $notificationStmt,
                "iiss",
                $patientId,
                $appointment_id,
                $title,
                $message
            );

            mysqli_stmt_execute($notificationStmt);
            mysqli_stmt_close($notificationStmt);
        }

        /* Send Firebase push notification */
        if ($patientId !== null) {
            sendFirebasePushToUser(
                $conn,
                (int) $patientId,
                $title,
                $message
            );
        }

        
        /* Activity Log */
        $activitySql = "
            INSERT INTO activity_logs
            (user_id, activity_type, action, description)
            VALUES (?, 'Appointment', 'Rescheduled', ?)
        ";

        $description = "A reschedule request was sent to "
            . $patientName . " for "
            . date("F j, Y", strtotime($date))
            . " at "
            . date("g:i A", strtotime($time))
            . ".";

        $activityStmt = mysqli_prepare($conn, $activitySql);

        if ($activityStmt) {
            mysqli_stmt_bind_param(
                $activityStmt,
                "is",
                $_SESSION['user_id'],
                $description
            );

            mysqli_stmt_execute($activityStmt);
            mysqli_stmt_close($activityStmt);
        }

        /* Clinic Activity Log */
        logClinicActivity(
            $conn,
            'Rescheduled',
            'Appointments',
            "A reschedule request was sent to {$patientName} for "
                . date("F j, Y", strtotime($date))
                . " at "
                . date("g:i A", strtotime($time))
                . "."
        );

        $_SESSION['success'] = "Reschedule Request Sent!";
        $_SESSION['success_message'] = "The patient has been notified and must confirm the proposed schedule.";

    }else{

        $_SESSION['error'] = "Failed to reschedule appointment.";

    }

    header("Location: ../clinic/appointments.php");
    exit();

}
?>

