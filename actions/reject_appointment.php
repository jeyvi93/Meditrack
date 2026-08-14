<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $appointment_id = $_POST['appointment_id'];
    $reason = $_POST['reason'];
    $note = $_POST['note'];

    $sql = "UPDATE appointments
            SET status = 'Rejected',
                reason = ?,
                note = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $reason,
        $note,
        $appointment_id
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Appointment rejected successfully.";
    } else {
        $_SESSION['error'] = "Failed to reject appointment.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../pages/appointments.php");
    exit();
}
?>