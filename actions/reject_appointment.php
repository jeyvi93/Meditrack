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

    /* Get patient name and user ID */
    $nameSql = "
        SELECT a.user_id, u.full_name
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        WHERE a.id = ?
    ";

    $nameStmt = mysqli_prepare($conn, $nameSql);
    mysqli_stmt_bind_param($nameStmt, "i", $appointment_id);
    mysqli_stmt_execute($nameStmt);

    $nameResult = mysqli_stmt_get_result($nameStmt);
    $appointment = mysqli_fetch_assoc($nameResult);

    $patientName = $appointment['full_name'] ?? 'Unknown patient';
    $patientId = $appointment['user_id'] ?? null;

    mysqli_stmt_close($nameStmt);

    /* Reject appointment */
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

        /* Notification */
        $notificationSql = "
            INSERT INTO notifications
            (user_id, appointment_id, title, message)
            VALUES (?, ?, ?, ?)
        ";

        $title = "Appointment Rejected";

        $message = "Your appointment request has been rejected. Reason: " . $reason;

        if (!empty(trim($note))) {
            $message .= " Notes: " . $note;
        }

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

        /* Activity Log */
        $activitySql = "
            INSERT INTO activity_logs
            (user_id, activity_type, action, description)
            VALUES (?, 'Appointment', 'Rejected', ?)
        ";

        $description = $patientName . "'s appointment was rejected.";

        $activityStmt = mysqli_prepare($conn, $activitySql);

        mysqli_stmt_bind_param(
            $activityStmt,
            "is",
            $_SESSION['user_id'],
            $description
        );

        mysqli_stmt_execute($activityStmt);
        mysqli_stmt_close($activityStmt);

        $_SESSION['success'] = "Appointment Rejected!";
        $_SESSION['success_message'] = "The patient has been notified.";

    } else {
        $_SESSION['error'] = "Failed to reject appointment.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/appointments.php");
    exit();
}
?>

