<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false
    ]);
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $notification_id = $_POST['notification_id'];
    $user_id = $_SESSION['user_id'];

    $sql = "
        UPDATE notifications
        SET is_read = 1
        WHERE id = ?
        AND user_id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $notification_id,
        $user_id
    );

    if (mysqli_stmt_execute($stmt)) {

        echo json_encode([
            "success" => true
        ]);

    } else {

        echo json_encode([
            "success" => false
        ]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    exit();
}
?>