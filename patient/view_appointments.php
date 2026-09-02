<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | MediTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/view_appointments.css"> <!-- Uses same logic as book.css -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<div class="bg-gradient"></div>

<!-- TOP NAVBAR (Matches Book Page) -->
<nav class="navbar main-wrapper">
    <div class="logo">
        <img src="../assets/images/med.png" alt="Logo">
    </div>
    <div class="desktop-menu">
        <a href="view_appointments.html" style="color: var(--primary-blue);">My Appointments</a>
        <a href="book_appointment.html">Book Appointment</a>
        <a href="notifications.html">Notifications</a>
        <a href="view_profile.html">Profile</a>
        <a href="../auth/logout.php" class="btn-logout-small" onclick="openLogoutModal(); return false;">Logout</a>
    </div>
</nav>

<main class="main-wrapper">
    <section class="booking-section">
        
        <div class="page-header">
            <h1>My <span class="text-accent">Appointments</span></h1>
            <p>View and manage your clinic visits and medical requests.</p>
        </div>

        <div class="booking-grid">
            <!-- Left Side: Information Card (Same as Book Page) -->
            <div class="info-side">
                <div class="status-card">
                    <div class="status-icon"><i class='bx bxs-time-five'></i></div>
                    <div>
                        <h4>Appointment Status</h4>
                        <p>Track your requests from pending to completion.</p>
                    </div>
                </div>
                
                <div class="reminder-list">
                    <h5>Quick Guide:</h5>
                    <ul>
                        <li><i class='bx bx-check-double'></i> <b>Pending:</b> Waiting for clinic review.</li>
                        <li><i class='bx bx-check-double'></i> <b>Approved:</b> Your slot is confirmed.</li>
                        <li><i class='bx bx-check-double'></i> <b>History:</b> Record of past visits.</li>
                    </ul>
                </div>
            </div>

            <!-- Right Side: The Content (Your Tab Contents) -->
            <div class="appointment-card">
                
                <!-- TAB NAVIGATION -->
                <div class="appointment-tabs">
                    <button class="tab-btn active" onclick="switchTab(event, 'pending')">Pending</button>
                    <button class="tab-btn" onclick="switchTab(event, 'approved')">Approved</button>
                    <button class="tab-btn" onclick="switchTab(event, 'completed')">History</button>
                    <button class="tab-btn" onclick="switchTab(event, 'cancelled')">Cancelled</button>
                </div>

                <!-- PENDING -->
                <div id="pending" class="tab-content active">
                    <div class="appointments-list-wrapper">
                        <div class="inner-card">
                            <div class="card-date-info">
                                <span class="day">27</span>
                                <span class="month">JUN</span>
                            </div>
                            <div class="card-text">
                                <span class="status-pill pending">Pending</span>
                                <h3>Medicine Request</h3>
                                <p><i class='bx bx-time'></i> 02:30 PM</p>
                            </div>
                            <button class="btn-cancel-mini" onclick="openModal()">Cancel</button>
                        </div>
                    </div>
                </div>

                <div id="pending" class="tab-content active">
                    <div class="appointments-list-wrapper">
                        <div class="inner-card">
                            <div class="card-date-info">
                                <span class="day">28</span>
                                <span class="month">JUN</span>
                            </div>
                            <div class="card-text">
                                <span class="status-pill pending">Pending</span>
                                <h3>Medicine Request</h3>
                                <p><i class='bx bx-time'></i> 02:30 PM</p>
                            </div>
                            <button class="btn-cancel-mini" onclick="openModal()">Cancel</button>
                        </div>
                    </div>
                </div>

                <div id="pending" class="tab-content active">
                    <div class="appointments-list-wrapper">
                        <div class="inner-card">
                            <div class="card-date-info">
                                <span class="day">29</span>
                                <span class="month">JUN</span>
                            </div>
                            <div class="card-text">
                                <span class="status-pill pending">Pending</span>
                                <h3>Medicine Request</h3>
                                <p><i class='bx bx-time'></i> 02:30 PM</p>
                            </div>
                            <button class="btn-cancel-mini" onclick="openModal()">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- APPROVED -->
                <div id="approved" class="tab-content">
                    <div class="appointments-list-wrapper">
                        <div class="inner-card">
                            <div class="card-date-info confirmed">
                                <span class="day">24</span>
                                <span class="month">JUN</span>
                            </div>
                            <div class="card-text">
                                <span class="status-pill confirmed">Approved</span>
                                <h3>General Check-up</h3>
                                <p><i class='bx bx-time'></i> 09:30 AM</p>
                            </div>
                            <button class="btn-cancel-mini" onclick="openModal()">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- COMPLETED (TABLE) -->
                <div id="completed" class="tab-content">
                    <div class="table-container">
                        <table class="appointment-table">
                            <thead>
                                <tr><th>Date</th><th>Purpose</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Jun 30, 2026</td><td>Dental Check-up</td><td><span class="status-pill completed">Completed</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CANCELLED (TABLE) -->
                <div id="cancelled" class="tab-content">
                    <div class="table-container">
                        <table class="appointment-table">
                            <thead>
                                <tr><th>Date</th><th>Purpose</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Jun 25, 2026</td><td>General Checkup</td><td><span class="status-pill cancelled">Rejected</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<!-- MOBILE BOTTOM TAB BAR -->
<nav class="bottom-nav">
    <a href="view_appointments.html" class="nav-item active">
        <i class='bx bx-calendar-event'></i>
        <span>My Appointments</span>
    </a>
    <a href="book_appointment.html" class="nav-item">
        <i class='bx bx-plus-circle'></i>
        <span>Book Appointment</span>
    </a>
    <a href="notifications.html" class="nav-item">
        <i class='bx bx-bell'></i>
        <span>Notifications</span>
    </a>
    <a href="view_profile.html" class="nav-item">
        <i class='bx bx-user-circle'></i>
        <span>Profile</span>
    </a>
</nav>

<!-- CANCEL MODAL (Hidden by default) -->
<div id="cancelModal" class="modal-overlay hidden">
    <div class="modal-box">
        <i class='bx bx-error-circle'></i>
        <h3>Cancel Appointment?</h3>
        <p>This action cannot be undone. Do you want to proceed?</p>
        <div class="modal-btns">
            <button class="btn-no" onclick="closeModal()">No, Keep it</button>
            <button class="btn-yes" onclick="confirmCancel()">Yes, Cancel</button>
        </div>
    </div>
</div>

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="modal-overlay hidden">
    <div class="modal-box">
        <i class='bx bx-log-out-circle'></i>

        <h3>Sign Out?</h3>

        <p>Are you sure you want to end your session on this device?</p>

        <div class="modal-btns">
            <button class="btn-no" onclick="closeLogoutModal()">
                Cancel
            </button>

            <button class="btn-yes" onclick="confirmLogout()">
                Logout
            </button>
        </div>
    </div>
</div>

<script>
    function switchTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(" active", ""); }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    function openModal() { document.getElementById('cancelModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('cancelModal').classList.add('hidden'); }
    
    function openLogoutModal() {
        document.getElementById('logoutModal')
            .classList.remove('hidden');
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal')
            .classList.add('hidden');
    }

    function confirmLogout() {
        window.location.href = "../auth/logout.php";
    }
</script>

</body>
</html>