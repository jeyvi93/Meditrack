<?php

function logClinicActivity(
    $conn,
    $actionType,
    $module,
    $description
) {

    // ==========================
    // GET PERSONNEL INFORMATION
    // ==========================

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $userId = (int) $_SESSION['user_id'];

    $userName = $_SESSION['full_name'] ?? 'Unknown';
    $userRole = $_SESSION['role'] ?? 'Unknown';


    // ==========================
    // INSERT ACTIVITY RECORD
    // ==========================

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO clinic_activity_logs
        (
            user_id,
            user_name,
            user_role,
            action_type,
            module,
            description
        )
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        error_log("Clinic Activity Log Error: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "isssss",
        $userId,
        $userName,
        $userRole,
        $actionType,
        $module,
        $description
    );

    $success = mysqli_stmt_execute($stmt);

    if (!$success) {
        error_log("Clinic Activity Log Error: " . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    return $success;
}