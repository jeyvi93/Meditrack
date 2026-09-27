<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | MediTrack</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/forgot_password.css">
</head>
<body>

<div class="bg-gradient"></div>

<!-- HOME PAGE NAVBAR (Consistent with Landing Page) -->
<nav class="navbar main-wrapper">
    <div class="logo">
        <img src="../assets/images/med.png" alt="MediTrack Logo">
    </div>
    <div class="nav-auth">
        <a href="register.php" class="register-link">Register</a>
        <a href="../login.php" class="btn-login">Login</a>
    </div>
</nav>

<!-- MOBILE TOP BAR (Back button on the left end) -->
<nav class="mobile-top-bar">
    <a href="../index.php" class="back-btn-mobile">
        <i class='bx bx-chevron-left'></i>
    </a>
    <span class="nav-title">Security Portal</span>
    <div class="spacer-nav"></div>  
</nav>

<main class="main-wrapper">
    <section class="split-layout">
        
        <!-- SIDEBAR (Desktop Only) -->
        <div class="info-side">
            <div class="status-card">
                <div class="status-icon"><i class='bx bxs-shield-alt-2'></i></div>
                <div>
                    <h4>Identity Verification</h4>
                    <p>Regain access to your clinic account securely.</p>
                </div>
            </div>
            
            <div class="reminder-list">
                <h5>Recovery Steps:</h5>
                <ul>
                    <li><i class='bx bx-check-double'></i> Enter your registered email address.</li>
                    <li><i class='bx bx-check-double'></i> Check your inbox for the reset link.</li>
                    <li><i class='bx bx-check-double'></i> Create a new, strong password.</li>
                </ul>
            </div>
        </div>

        <!-- FORM CARD -->
        <div class="appointment-card">
            <div class="card-header">
                <div class="icon-circle"><i class='bx bx-key'></i></div>
                <div class="header-text">
                    <h2>Forgot Password?</h2>
                    <p>Enter your email and we'll send you a recovery link.</p>
                </div>
            </div>

            <form action="../api/auth/forgot_password.php" method="POST" class="forgot-form">
                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-box">
                        <i class='bx bx-envelope'></i>
                        <input type="email" name="email" placeholder="Enter your registered email address" required>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn-submit">
                        Send Reset Link <i class='bx bx-paper-plane'></i>
                    </button>
                    <a href="../index.php" class="back-link-mobile">Back to Login</a>
                </div>
            </form>
        </div>
    </section>
</main>

<!-- SUCCESS MODAL -->
<div id="forgotModal" class="modal-overlay hidden">
    <div class="modal-box">
        
        <i class='bx bxs-envelope-open success-icon'></i>

        <h3>Check Your Email</h3>

        <p>
            A password reset link has been sent to your email.
            Please check your inbox to continue.
        </p>

        <button 
            type="button" 
            class="btn-submit modal-login-btn"
            onclick="window.location.href='../index.php#login'">
            Return to Login
        </button>

    </div>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get("sent") === "1") {

        const modal = document.getElementById("forgotModal");

        if (modal) {
            modal.classList.remove("hidden");

            setTimeout(() => {
                modal.classList.add("show");
            }, 50);
        }
    }
</script>

</body>
</html>