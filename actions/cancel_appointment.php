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


    /* Get patient name */
    $nameSql = "
        SELECT u.full_name
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

    mysqli_stmt_close($nameStmt);


    /* Cancel appointment */
    $sql = "
        UPDATE appointments
        SET
            status = 'Cancelled',
            reason = ?,
            note = ?
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $reason,
        $note,
        $appointment_id
    );


    if (mysqli_stmt_execute($stmt)) {

        /* Activity Log */
        $activitySql = "
            INSERT INTO activity_logs
            (user_id, activity_type, action, description)
            VALUES (?, 'Appointment', 'Cancelled', ?)
        ";

        $description = $patientName . "'s appointment was cancelled.";

        $activityStmt = mysqli_prepare($conn, $activitySql);

        mysqli_stmt_bind_param(
            $activityStmt,
            "is",
            $_SESSION['user_id'],
            $description
        );

        mysqli_stmt_execute($activityStmt);
        mysqli_stmt_close($activityStmt);


        $_SESSION['success'] = "Appointment cancelled successfully.";

    } else {

        $_SESSION['error'] = "Failed to cancel appointment.";

    }


    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/appointments.php");
    exit();

}

?>