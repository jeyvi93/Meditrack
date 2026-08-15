<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $appointment_id = $_POST['appointment_id'];

    $sql = "UPDATE appointments
            SET status = 'Completed'
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $appointment_id);

    if (mysqli_stmt_execute($stmt)) {
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