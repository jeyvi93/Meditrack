<?php

function logClinicActivity(
    $conn,
    $actionType,
    $module,
    $description
) {

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $userId = (int) $_SESSION['user_id'];

    // ==========================
    // GET PERSONNEL INFORMATION
    // ==========================

    $userSql = "
        SELECT
            u.role,
            COALESCE(
                NULLIF(TRIM(CONCAT_WS(' ',
                    NULLIF(TRIM(st.first_name), ''),
                    NULLIF(TRIM(st.last_name), '')
                )), ''),
                NULLIF(TRIM(CONCAT_WS(' ',
                    NULLIF(TRIM(s.first_name), ''),
                    NULLIF(TRIM(s.last_name), '')
                )), ''),
                'Unknown'
            ) AS user_name
        FROM users u
        LEFT JOIN staffs st ON u.staff_id = st.id
        LEFT JOIN students s ON u.student_id = s.id
        WHERE u.id = ?
        LIMIT 1
    ";

    $userStmt = mysqli_prepare($conn, $userSql);

    if (!$userStmt) {
        error_log("Clinic Activity Log User Lookup Error: " . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($userStmt, "i", $userId);
    mysqli_stmt_execute($userStmt);

    $userResult = mysqli_stmt_get_result($userStmt);
    $userData = mysqli_fetch_assoc($userResult);

    mysqli_stmt_close($userStmt);

    if (!$userData) {
        error_log("Clinic Activity Log Error: User not found. ID: " . $userId);
        return false;
    }

    $userName = $userData['user_name'];
    $userRole = $userData['role'] ?: ($_SESSION['role'] ?? 'Unknown');


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