<?php
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $appointment_id = $_POST['appointment_id'];

    /* Get patient ID, name, and current status */
    $patientSql = "
        SELECT a.user_id, u.full_name, a.status
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

    mysqli_stmt_close($patientStmt);

    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found.";
        mysqli_close($conn);
        header("Location: ../clinic/appointments.php");
        exit();
    }

    $patientId = $appointment['user_id'];
    $patientName = $appointment['full_name'];
    $currentStatus = $appointment['status'];

    if ($currentStatus === 'Completed') {
        $_SESSION['success'] = "Appointment Already Completed";
        $_SESSION['success_message'] = "This appointment has already been completed.";
        mysqli_close($conn);
        header("Location: ../clinic/appointments.php#completed");
        exit();
    }

    $sql = "UPDATE appointments
            SET status = 'Completed'
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $appointment_id);

    if (mysqli_stmt_execute($stmt)) {

        /* Notification */
        $notificationSql = "
            INSERT INTO notifications
            (user_id, appointment_id, title, message)
            VALUES (?, ?, ?, ?)
        ";

        $title = "Appointment Completed";
        $message = "Your appointment has been marked as completed.";

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
                "Appointment Completed",
                "Your appointment has been marked as completed."
            );
        }

        /* Activity Log */
        $activitySql = "
            INSERT INTO activity_logs
            (user_id, activity_type, action, description)
            VALUES (?, 'Appointment', 'Completed', ?)
        ";

        $description = $patientName . "'s appointment was marked as completed.";

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
                'Completed',
                'Appointments',
                "{$patientName}'s appointment was marked as completed."
            );

        $_SESSION['success'] = "Appointment Completed!";
        $_SESSION['success_message'] = "The appointment has been completed.";

    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/appointments.php#completed");
    exit();
}
?>

