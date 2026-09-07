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
                <div class="noti-stats-icon"><i class='bx bxs-bell'></i></div>
                <h4>All Caught Up?</h4>
                <p>You have <strong>1 unread</strong> notifications today.</p>
            </div>
        </aside>

        <!-- Notification List -->
        <section class="notification-feed">
            
            <div class="date-divider">Today</div>

            <div class="noti-card unread">
                <div class="unread-dot"></div>
                <div class="noti-icon-box icon-green">
                    <i class='bx bx-check-double'></i>
                </div>
                <div class="noti-content">
                    <div class="noti-meta">
                        <h4>Appointment Approved</h4>
                        <span class="noti-time">10:30 AM</span>
                    </div>
                    <p class="noti-text">Your request for a dental checkup on Oct 28 has been confirmed. Please bring your ID.</p>
                    <div class="noti-actions">
                        <button class="btn-noti-primary" onclick="window.location.href='view_appointments.php'">View Appointment</button>
                        <button class="btn-noti-secondary" onclick="markAsRead(this)">Mark as Read</button>
                    </div>
                </div>
            </div>


        </section>
    </div>
</main>


<script src="../assets/js/notifications.js"></script>

</body>
</html>