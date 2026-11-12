<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

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

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $appointment_id = (int) ($_GET['appointment_id'] ?? 0);

    if ($appointment_id <= 0) {
        $_SESSION['error'] = "Invalid appointment.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    $stmt = $conn->prepare("
        SELECT status
        FROM appointments
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        $_SESSION['error'] = "Unable to verify appointment.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    if ($appointment['status'] === 'Completed') {
        $_SESSION['success'] = "Appointment Already Completed";
        $_SESSION['success_message'] = "This appointment has already been completed.";
        header("Location: ../clinic/appointments.php#completed");
        exit();
    }

    if ($appointment['status'] !== 'Approved') {
        $_SESSION['error'] = "Only approved appointments can be completed.";
        header("Location: ../clinic/appointments.php");
        exit();
    }

    header(
        "Location: ../clinic/consultation.php?appointment_id="
        . $appointment_id
    );
    exit();
}
?>

