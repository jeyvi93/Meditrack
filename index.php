<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit();
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
                <a href="#" onclick="openLogin()" class="btn-login">Login</a>
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
                <h1>Integrated School <span class="text-accent">Clinic Management System.</span></h1>
                <p>A centralized platform for managing health records, clinic consultations, and medical services within the academic institution.</p>
                
                <div class="trust-indicators">
                    <div class="indicator">
                        <i class='bx bxs-check-shield'></i>
                        <span>Secure Records</span>
                    </div>
                    <div class="indicator">
                        <i class='bx bxs-time-five'></i>
                        <span>On-Campus Care</span>
                    </div>
                </div>

                <div class="cta-group">
                    <button class="btn-lg" onclick="installApp()">
                         Install Student App <i class='bx bx-download'></i>
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
<!-- LOGIN MODAL -->
<div id="loginModal" class="modal">

    <div class="modal-content login-modal">

        <span class="close" onclick="closeLogin()">&times;</span>

        <div class="login-box">

            <h2>User Login</h2><br>

            <form action="auth/login_process.php" method="POST">

                <!-- EMAIL -->
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <div class="input-box">
                        <i class='bx bx-user'></i>
                        <input type="email" id="email" name="email" placeholder="Enter email address" required> 
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="input-group">

                    <div class="password-header">
                        <label for="password">Password</label>
                        <a href="patient/forgot_password.html" class="forgot-link">Forgot Password?</a>
                    </div>

                    <div class="input-box">
                        <i class='bx bx-lock-alt'></i>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>

                </div>

                <button type="submit" class="btn-login">Login</button>

            </form>

            <div class="bottom-text">
                <p>Don't have an account?</p>
                <a href="patient/register.html">Register here</a>
            </div>

        </div>

    </div>

</div>
<script src="assets/js/index.js"></script>

</body>
</html>





