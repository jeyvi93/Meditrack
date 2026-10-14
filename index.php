<?php
session_start();

if (isset($_SESSION['user_id'])) {

    if (
        $_SESSION['role'] == "Admin" ||
        $_SESSION['role'] == "Clinic Staff"
    ) {

        header("Location: clinic/dashboard.php");
        exit();

    } elseif (
        $_SESSION['role'] == "Student" ||
        $_SESSION['role'] == "Teaching Staff" ||
        $_SESSION['role'] == "Non-Teaching Staff"
    ) {

        header("Location: patient/view_appointments.php");
        exit();

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#800000">
    <link rel="apple-touch-icon" href="assets/images/icon-192.png">
    <title>MediTrack | Clinic Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <div class="bg-gradient"></div>

    <header class="navbar-wrapper">
        <nav class="navbar">
            <div class="logo">
                <img src="assets/images/med.png" alt="MediTrack Logo">
            </div>
            <div class="nav-auth">
                <a href="patient/register.php" class="home-link">Register</a>
                <a href="login.php" class="btn-login">Login</a>
            </div>
        </nav>
    </header>

    <main class="main-wrapper">
        <section class="hero">
            <!-- Left Side: Content -->
            <div class="hero-text">
                <div class="uni-tag">
                    <span class="pulse"></span> Mandaue City College
                </div>
                <h1>Integrated Campus <span class="text-accent">Clinic Management System.</span></h1>
                <p>A centralized platform designed to streamline clinic services, health records, consultations, appointments, and other healthcare needs within the academic institution.</p>
                
                <div class="trust-indicators">
                    <div class="indicator">
                        <i class='bx bxs-check-shield'></i>
                        <span>Secure Records</span>
                    </div>
                    <div class="indicator">
                        <i class='bx bxs-time-five'></i>
                        <span>Efficient Clinic Services</span>
                    </div>
                </div>

                <div class="cta-group">
                    <button id="installAppButton" class="btn-lg" onclick="openInstallModal()">
                         Get MediTrack App <i class='bx bx-download'></i>
                    </button>
                    <div id="installStatusBadge" class="status-badge">
                        <span class="dot"></span> Available on Mobile
                    </div>
                </div>
            </div>

            <!-- Right Side: Image (Hidden on Mobile) -->
            <div class="hero-image">
                <div class="image-container">
                    <!-- Replace the src with your actual image path -->
                    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&q=80&w=1000" alt="Medical Professional">
                    <div class="floating-card">
                        <i class='bx bxs-heart-circle'></i>
                        <span>Prioritizing Campus-Wide Wellness</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Install App Modal -->
<div id="installModal" class="install-modal">
    <div class="install-modal-content">

        <button type="button"
                class="install-modal-close"
                onclick="closeInstallModal()"
                aria-label="Close modal">
            &times;
        </button>

        <h2>Install MediTrack</h2>
        <p class="install-modal-subtitle">
            Add MediTrack to your home screen for easier access.
        </p>

        <!-- Android Instructions -->
        <div id="androidInstructions" class="install-instructions">
            <h3><i class='bx bxl-android'></i> Android</h3>

            <ol>
                <li>Tap the Install MediTrack button below.</li>
                <li>Confirm the installation in your browser.</li>
                <li>MediTrack will be added to your home screen.</li>
            </ol>

            <button type="button"
                    id="installConfirmButton"
                    class="btn-lg install-confirm-btn"
                    onclick="installApp()">
                Install MediTrack
                <i class='bx bx-download'></i>
            </button>

            <p id="androidInstallMessage" class="install-message"></p>
        </div>

        <!-- iOS Instructions -->
        <div id="iosInstructions" class="install-instructions">
            <h3><i class='bx bxl-apple'></i> iPhone / iPad</h3>
            <ol>
                <li>Open MediTrack in Safari.</li>
                <li>Tap the Share icon.</li>
                <li>Scroll down and tap <strong>View More</strong> if needed.</li>
                <li>Select <strong>Add to Home Screen</strong>.</li>
                <li>Tap <strong>Add</strong> to finish.</li>
            </ol>

            <p class="install-message">
                iOS installation must be completed through the Safari menu.
            </p>
        </div>

    </div>
</div>
<script src="assets/js/index.js"></script>

</body>
</html>





