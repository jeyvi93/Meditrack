<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

require_once "../config/database.php";

$userId = (int) $_SESSION['user_id'];

/* Get navbar badge count */
$viewedSql = "
    SELECT COUNT(*) AS unread_count
    FROM notifications
    WHERE user_id = ?
    AND is_viewed = 0
";

$viewedStmt = mysqli_prepare($conn, $viewedSql);
mysqli_stmt_bind_param($viewedStmt, "i", $userId);
mysqli_stmt_execute($viewedStmt);

$viewedResult = mysqli_stmt_get_result($viewedStmt);
$viewedData = mysqli_fetch_assoc($viewedResult);

$badgeCount = (int) ($viewedData['unread_count'] ?? 0);

mysqli_stmt_close($viewedStmt);

/* Get unread notification count */
$readSql = "
    SELECT COUNT(*) AS unread_count
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
";

$readStmt = mysqli_prepare($conn, $readSql);
mysqli_stmt_bind_param($readStmt, "i", $userId);
mysqli_stmt_execute($readStmt);

$readResult = mysqli_stmt_get_result($readStmt);
$readData = mysqli_fetch_assoc($readResult);

$unreadCount = (int) ($readData['unread_count'] ?? 0);

mysqli_stmt_close($readStmt);

/* Get notifications for live updates */
$notificationsSql = "
    SELECT id, title, message, is_read, is_viewed, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC, id DESC
    LIMIT 50
";

$notificationsStmt = mysqli_prepare($conn, $notificationsSql);
mysqli_stmt_bind_param($notificationsStmt, "i", $userId);
mysqli_stmt_execute($notificationsStmt);

$notificationsResult = mysqli_stmt_get_result($notificationsStmt);

$notifications = [];

while ($row = mysqli_fetch_assoc($notificationsResult)) {
    $notifications[] = $row;
}

mysqli_stmt_close($notificationsStmt);
mysqli_close($conn);

echo json_encode([
    'success' => true,
    'badge_count' => $badgeCount,
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);
