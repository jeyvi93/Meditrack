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
                <a href="patient/register.php" class="home-link">Create Account</a>
                <a href="login.php" class="btn-login">Login</a>
            </div>
        </nav>
    </header>

    
    <main class="landing-main">

        <!-- HERO SECTION -->
        <section class="landing-hero">
            <div class="hero-content">
                <span class="hero-label">
                    MANDAUE CITY COLLEGE
                </span>

                <h1>
                    Appointments
                    <span>made easier.</span>
                </h1>

                <p class="hero-description">
                    MediTrack makes scheduling appointments with the Mandaue City College
                    clinic easier. Request a clinic visit, keep track of your appointments,
                    and receive updates about your schedule.
                </p>

                <div class="hero-actions">
                    <a href="patient/register.php" class="hero-primary-btn">
                        Get Started
                        <i class='bx bx-right-arrow-alt'></i>
                    </a>

                    <a href="#how-it-works" class="hero-secondary-btn">
                        How It Works
                    </a>
                </div>

                <!-- MOBILE INSTALL BUTTON -->
                <div class="mobile-install-area">
                    <button
                        id="installAppButton"
                        class="mobile-install-btn"
                        onclick="openInstallModal()"
                    >
                        <i class='bx bx-download'></i>
                        Install MediTrack App
                    </button>

                    <div id="installStatusBadge" class="status-badge">
                        <i class='bx bx-check-circle'></i>
                        App installation available
                    </div>
                </div>

                <div class="hero-note">
                    <i class='bx bx-check-circle'></i>
                    Connecting the MCC community and clinic personnel
                </div>
            </div>

            <!-- APPOINTMENT PREVIEW -->
            <div class="appointment-preview">
                <div class="preview-top">
                    <div>
                        <span class="preview-caption">MEDITRACK</span>
                        <h3>Appointment Overview</h3>
                        <p>Your clinic appointments at a glance.</p>
                    </div>

                    <div class="preview-icon">
                        <i class='bx bx-calendar-check'></i>
                    </div>
                </div>

                <div class="preview-highlight">
                    <div class="preview-highlight-icon">
                        <i class='bx bx-calendar-plus'></i>
                    </div>

                    <div>
                        <small>APPOINTMENT ACCESS</small>
                        <h4>Plan your clinic visit</h4>
                        <span>Request an appointment conveniently.</span>
                    </div>
                </div>

                <div class="preview-list">
                    <div class="preview-row">
                        <div class="preview-row-icon blue-icon">
                            <i class='bx bx-calendar'></i>
                        </div>
                        <div>
                            <strong>Appointment Scheduling</strong>
                            <small>Submit your appointment request.</small>
                        </div>
                        <i class='bx bx-chevron-right'></i>
                    </div>

                    <div class="preview-row">
                        <div class="preview-row-icon teal-icon">
                            <i class='bx bx-time-five'></i>
                        </div>
                        <div>
                            <strong>Appointment Tracking</strong>
                            <small>Check your appointment status.</small>
                        </div>
                        <i class='bx bx-chevron-right'></i>
                    </div>

                    <div class="preview-row">
                        <div class="preview-row-icon purple-icon">
                            <i class='bx bx-bell'></i>
                        </div>
                        <div>
                            <strong>Status Updates</strong>
                            <small>Stay informed about your requests.</small>
                        </div>
                        <i class='bx bx-chevron-right'></i>
                    </div>
                </div>

                <div class="preview-footer">
                    <i class='bx bx-shield-check'></i>
                    Making clinic appointments easier for the MCC community.
                </div>
            </div>
        </section>


        <!-- APPOINTMENT FEATURES -->
        <section class="appointment-features" id="how-it-works">

            <div class="section-heading">
                <span>MADE FOR YOUR CONVENIENCE</span>
                <h2>Clinic appointments made simpler.</h2>
                <p>
                    Spend less time figuring out your clinic visit and more time
                    focusing on what matters.
                </p>
            </div>

            <div class="appointment-card-grid">

                <article class="appointment-card">
                    <div class="feature-icon feature-blue">
                        <i class='bx bx-calendar-plus'></i>
                    </div>
                    <h3>Request Appointments</h3>
                    <p>
                        Submit your preferred clinic appointment schedule
                        through MediTrack.
                    </p>
                </article>

                <article class="appointment-card">
                    <div class="feature-icon feature-teal">
                        <i class='bx bx-calendar-check'></i>
                    </div>
                    <h3>Track Your Schedule</h3>
                    <p>
                        View your appointments and check their current status
                        whenever you need to.
                    </p>
                </article>

                <article class="appointment-card">
                    <div class="feature-icon feature-purple">
                        <i class='bx bx-bell'></i>
                    </div>
                    <h3>Stay Updated</h3>
                    <p>
                        Receive updates about your appointment requests
                        and changes to their status.
                    </p>
                </article>

            </div>
        </section>


        <!-- HOW IT WORKS -->
        <section class="how-section">
            <div class="how-content">
                <span class="how-label">GET STARTED IN THREE STEPS</span>
                <h2>Your next clinic appointment starts here.</h2>
                <p>
                    MediTrack provides a convenient way for students and school
                    personnel to request and manage their clinic appointments.
                </p>
            </div>

            <div class="steps-container">
                <div class="step-item">
                    <span class="step-number">01</span>
                    <div>
                        <h3>Access Your Account</h3>
                        <p>Register or log in to your MediTrack account.</p>
                    </div>
                </div>

                <div class="step-item">
                    <span class="step-number">02</span>
                    <div>
                        <h3>Request a Schedule</h3>
                        <p>Choose your preferred appointment date and time.</p>
                    </div>
                </div>

                <div class="step-item">
                    <span class="step-number">03</span>
                    <div>
                        <h3>Track Your Appointment</h3>
                        <p>Check the status of your appointment request.</p>
                    </div>
                </div>
            </div>
        </section>


        <!-- FINAL CTA -->
        <section class="landing-bottom-cta">
            <div>
                <span>APPOINTMENTS MADE EASIER AT MCC</span>
                <h2>Ready to schedule your clinic visit?</h2>
                <p>Access MediTrack and manage your clinic appointments conveniently.</p>
            </div>

            <a href="patient/register.php" class="bottom-cta-btn">
                Get Started
                <i class='bx bx-right-arrow-alt'></i>
            </a>
        </section>

        <footer class="landing-footer">
            <p>© 2026 MediTrack. All rights reserved.</p>
            <span>MediTrack | Appointments Made Easier</span>
        </footer>

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





