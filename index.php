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
    <title>MediTrack | School Clinic Management</title>
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
                <a href="patient/register.html" class="register-link">Register</a>
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
                <p>A centralized platform for managing health records, clinic consultations, and medical services within the academic institution.</p>
                
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
                    <button class="btn-lg" onclick="installApp()">
                         Get MediTrack App <i class='bx bx-download'></i>
                    </button>
                    <div class="status-badge">
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

<script src="assets/js/index.js"></script>

</body>
</html>





