<?php

session_start();

require_once "../config/database.php";


if($_SERVER["REQUEST_METHOD"] == "POST"){

    $appointment_id = $_POST['appointment_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];


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

        $_SESSION['success'] = "Appointment rescheduled successfully.";

    }else{

        $_SESSION['error'] = "Failed to reschedule appointment.";

    }


    header("Location: ../clinic/appointments.php");
    exit();

}

?>