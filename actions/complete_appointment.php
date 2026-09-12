<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $appointment_id = $_POST['appointment_id'];

    /* Get patient ID */
    $patientSql = "
        SELECT user_id
        FROM appointments
        WHERE id = ?
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

    mysqli_stmt_close($patientStmt);

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

        $_SESSION['success'] = "Appointment marked as completed.";

    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/appointments.php#completed");
    exit();
}
?>

