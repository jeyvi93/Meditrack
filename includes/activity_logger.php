<?php

function logActivity($conn, $user_id, $activity_type, $action, $description)
{
    $sql = "
        INSERT INTO activity_logs
        (user_id, activity_type, action, description)
        VALUES (?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "isss",
        $user_id,
        $activity_type,
        $action,
        $description
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
}