<?php

session_start();

require_once "../config/database.php";
require_once __DIR__ . '/../config/firebase_messaging.php';

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
    $time = $_POST['appointment_time'];

    
    
    /* Get patient name and user ID */
    $patientSql = "
        SELECT a.user_id, u.full_name
        FROM appointments a
        JOIN users u ON a.user_id = u.id
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
    $patientName = $appointment['full_name'] ?? 'Unknown patient';

    mysqli_stmt_close($patientStmt);

    $sql = "
        UPDATE appointments
        SET
            appointment_date = ?,
            appointment_time = ?
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conn,$sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $date,
        $time,
        $appointment_id
    );

    if(mysqli_stmt_execute($stmt)){

        /* Notification */
        $notificationSql = "
            INSERT INTO notifications
            (user_id, appointment_id, title, message)
            VALUES (?, ?, ?, ?)
        ";

        $title = "Appointment Rescheduled";

        $message = "Your appointment has been rescheduled to "
                 . date("F j, Y", strtotime($date))
                 . " at "
                 . date("g:i A", strtotime($time))
                 . ".";

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

        $description = $patientName . "'s appointment was rescheduled to "
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


        $_SESSION['success'] = "Appointment Rescheduled!";
        $_SESSION['success_message'] = "The patient has been notified.";

    }else{

        $_SESSION['error'] = "Failed to reschedule appointment.";

    }

    header("Location: ../clinic/appointments.php");
    exit();

}
?>

