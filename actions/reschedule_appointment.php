<?php

session_start();

require_once "../config/database.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $appointment_id = $_POST['appointment_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];

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

        $_SESSION['success'] = "Appointment rescheduled successfully.";

    }else{

        $_SESSION['error'] = "Failed to reschedule appointment.";

    }

    header("Location: ../clinic/appointments.php");
    exit();

}
?>

