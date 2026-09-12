<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/database.php";

/* Get user's notifications */
$userId = $_SESSION['user_id'];

$sql = "
    SELECT id, title, message, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/* Get unread count */
$unreadSql = "
    SELECT COUNT(*) AS unread_count
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
";

$unreadStmt = mysqli_prepare($conn, $unreadSql);

mysqli_stmt_bind_param(
    $unreadStmt,
    "i",
    $userId
);

mysqli_stmt_execute($unreadStmt);

$unreadResult = mysqli_stmt_get_result($unreadStmt);
$unreadData = mysqli_fetch_assoc($unreadResult);

$unreadCount = $unreadData['unread_count'] ?? 0;


/* Get latest notification ID */

$latestNotificationSql = "
    SELECT id
    FROM notifications
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 1
";

$latestStmt = mysqli_prepare($conn, $latestNotificationSql);

mysqli_stmt_bind_param(
    $latestStmt,
    "i",
    $userId
);

mysqli_stmt_execute($latestStmt);

$latestResult = mysqli_stmt_get_result($latestStmt);
$latestNotification = mysqli_fetch_assoc($latestResult);

/*
 * Remember the latest notification that existed
 * when the user opened the notifications page.
 */
$_SESSION['notifications_last_viewed_id'] =
    $latestNotification['id'] ?? 0;

mysqli_stmt_close($latestStmt);
mysqli_stmt_close($unreadStmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | MediTrack</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

<div class="bg-gradient"></div>
    
<?php include '../includes/navbar.php'; ?>

<main class="main-wrapper">

    <header class="page-header">
        <h1>Your <span class="text-accent">Notifications</span></h1>
        <p>Updates regarding your clinic visits and account.</p>
    </header>


    <div class="notification-grid">

        <!-- Sidebar -->
        <aside class="info-side">

            <div class="summary-card">

                <div class="noti-stats-icon">
                    <i class='bx bxs-bell'></i>
                </div>

                <h4>All Caught Up?</h4>

                <p>
                    You have

                    <strong>
                        <?= $unreadCount ?>
                        unread
                        <?= $unreadCount == 1 ? 'notification' : 'notifications' ?>
                    </strong>.
                </p>

            </div>

        </aside>


        <!-- Notification List -->
        <section class="notification-feed">
            
            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php $currentDate = null; ?>

                <?php while ($notification = mysqli_fetch_assoc($result)): ?>

                    <?php

                    $notificationDate = date(
                        "Y-m-d",
                        strtotime($notification['created_at'])
                    );

                    if ($notificationDate !== $currentDate):

                        $currentDate = $notificationDate;

                        if ($notificationDate === date("Y-m-d")) {

                            $dateLabel = "Today";

                        } elseif (
                            $notificationDate ===
                            date("Y-m-d", strtotime("-1 day"))
                        ) {

                            $dateLabel = "Yesterday";

                        } else {

                            $dateLabel = date(
                                "F j, Y",
                                strtotime($notification['created_at'])
                            );
                        }

                    ?>

                        <div class="date-divider">
                            <?= htmlspecialchars($dateLabel) ?>
                        </div>

                    <?php endif; ?>


                    <?php

                    $iconClass = "bx-bell";
                    $iconColor = "icon-green";

                    if ($notification['title'] === "Appointment Approved") {

                        $iconClass = "bx-check-double";
                        $iconColor = "icon-green";

                    } elseif ($notification['title'] === "Appointment Rejected") {

                        $iconClass = "bx-x-circle";
                        $iconColor = "icon-red";

                    } elseif ($notification['title'] === "Appointment Cancelled") {

                        $iconClass = "bx-calendar-x";
                        $iconColor = "icon-red";

                    } elseif ($notification['title'] === "Appointment Completed") {

                        $iconClass = "bx-check-circle";
                        $iconColor = "icon-green";

                    } elseif ($notification['title'] === "Appointment Rescheduled") {

                        $iconClass = "bx-calendar-edit";
                        $iconColor = "icon-blue";
                    }


                    $cardClass = $notification['is_read'] == 0
                        ? "noti-card unread"
                        : "noti-card";

                    ?>


                    <div
                        class="<?= $cardClass ?>"
                        data-id="<?= $notification['id'] ?>"
                    >

                        <?php if ($notification['is_read'] == 0): ?>

                            <div class="unread-dot"></div>

                        <?php endif; ?>


                        <div class="noti-icon-box <?= $iconColor ?>">

                            <i class='bx <?= $iconClass ?>'></i>

                        </div>


                        <div class="noti-content">


                            <div class="noti-meta">

                                <h4>
                                    <?= htmlspecialchars($notification['title']) ?>
                                </h4>

                                <span class="noti-time">

                                    <?= date(
                                        "g:i A",
                                        strtotime($notification['created_at'])
                                    ) ?>

                                </span>

                            </div>


                            <?php if (
                                $notification['title'] === "Appointment Rejected" ||
                                $notification['title'] === "Appointment Cancelled"
                            ): ?>

                                <?php

                                $messageParts = explode(
                                    " Reason: ",
                                    $notification['message'],
                                    2
                                );

                                $mainMessage = $messageParts[0];

                                $reasonAndNotes = $messageParts[1] ?? '';

                                $reasonParts = explode(
                                    " Notes: ",
                                    $reasonAndNotes,
                                    2
                                );

                                $reason = $reasonParts[0] ?? '';

                                $notes = $reasonParts[1] ?? '';

                                ?>


                                <div class="noti-text">

                                    <p>
                                        <?= htmlspecialchars($mainMessage) ?>
                                    </p>


                                    <?php if (!empty($reason)): ?>

                                        <p class="noti-detail">

                                            <strong>Reason:</strong>

                                            <?= htmlspecialchars($reason) ?>

                                        </p>

                                    <?php endif; ?>


                                    <?php if (!empty($notes)): ?>

                                        <p class="noti-detail">

                                            <strong>Notes:</strong>

                                            <?= htmlspecialchars($notes) ?>

                                        </p>

                                    <?php endif; ?>

                                </div>


                            <?php else: ?>


                                <p class="noti-text">

                                    <?= htmlspecialchars($notification['message']) ?>

                                </p>


                            <?php endif; ?>


                            <div class="noti-actions">


                                <button
                                    class="btn-noti-primary"
                                    onclick="window.location.href='view_appointments.php'"
                                >
                                    View Appointment
                                </button>


                                <?php if ($notification['is_read'] == 0): ?>

                                    <button
                                        class="btn-noti-secondary"
                                        onclick="markAsRead(this)"
                                    >
                                        Mark as Read
                                    </button>

                                <?php endif; ?>


                            </div>


                        </div>

                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <div class="noti-card">

                    <div class="noti-icon-box icon-green">

                        <i class='bx bx-bell-off'></i>

                    </div>


                    <div class="noti-content">

                        <div class="noti-meta">

                            <h4>No Notifications</h4>

                        </div>


                        <p class="noti-text">

                            You don't have any notifications yet.

                        </p>

                    </div>

                </div>


            <?php endif; ?>


        </section>

    </div>

</main>


<script src="../assets/js/notifications.js"></script>

</body>
</html>