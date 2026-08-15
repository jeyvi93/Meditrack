<?php
session_start();

require_once "../config/database.php";

$id = $_POST['appointment_id'];

$sql = "UPDATE appointments
        SET status='Approved'
        WHERE id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$id);

mysqli_stmt_execute($stmt);

$_SESSION['success']="Appointment approved.";

header("Location: ../clinic/appointments.php");
exit();