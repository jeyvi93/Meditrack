<?php
session_start();

$error = $_GET['error'] ?? '';
$resend = $_GET['resend'] ?? '';
$loginEmail = $_SESSION['login_email'] ?? '';

unset($_SESSION['login_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MediTrack</title>

    <!-- Shared Styles -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Login Page Styles -->
    <link rel="stylesheet" href="assets/css/login.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="bg-gradient"></div>

    <div class="main-wrapper">

        <!-- NAVBAR -->
        <nav class="navbar">
            <div class="logo">
                <img src="assets/images/med.png" alt="MediTrack Logo">
            </div>

            <div class="nav-auth">
                <a href="index.php" class="home-link">Home</a>
                <a href="patient/register.php" class="btn-login">Register</a>
            </div>
        </nav>


        <!-- LOGIN SECTION -->
        <section class="login-section">

            <div class="login-card">

                <!-- LEFT SIDE -->
                <div class="login-info">

                    <div class="login-info-content">

                        <span class="badge">Welcome Back</span>

                        <h2>
                            Connected care,
                            <span>made simple.</span>
                        </h2>

                        <p>
                            Access your MediTrack account to stay 
                            connected with your campus clinic and 
                            access essential health services.
                        </p>

                        <ul class="benefit-list">

                            <li>
                                <i class="bx bx-lock-alt"></i>
                                Secure Account Access
                            </li>

                            <li>
                                <i class="bx bx-plus-medical"></i>
                                Connected Campus Healthcare
                            </li>

                            <li>
                                <i class="bx bx-heart"></i>
                                Convenient Health Services
                            </li>

                        </ul>

                    </div>

                </div>


                <!-- RIGHT SIDE -->
                <div class="login-form-container">

                    <div class="form-header">
                        <h1>Sign In</h1>
                        <p>Enter your account details to continue.</p>
                    </div>


                    <form action="auth/login_process.php" method="POST" class="login-form">

                        <!-- EMAIL -->
                        <div class="input-group">

                            <label for="email">
                                Email Address
                            </label>

                            <div class="input-box">

                                <i class="fas fa-envelope"></i>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email"
                                    value="<?php echo htmlspecialchars($loginEmail); ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- PASSWORD -->
                        <div class="input-group">

                            <div class="password-header">

                                <label for="password">
                                    Password
                                </label>

                                <a href="patient/forgot_password.php" class="forgot-link">
                                    Forgot Password?
                                </a>

                            </div>

                            <div class="input-box password-box">

                                <i class="fas fa-lock"></i>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                >

                                <i class="fas fa-eye password-toggle"
                                onclick="togglePassword('password', this)"></i>

                            </div>
                        </div>

                        <div class="login-error-container">
                            <?php if ($error === '1'): ?>
                                <span class="login-error">
                                    <i class='bx bx-error-circle'></i>
                                    Invalid email or password.
                                </span>

                            <?php elseif ($error === 'not_verified'): ?>
                                <span class="login-error">
                                    <i class='bx bx-error-circle'></i>
                                    Please verify your email first.
                                </span>

                                <a href="#" class="resend-link" onclick="resendVerification(event)">
                                    Resend email
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- LOGIN BUTTON -->
                        <button type="submit" class="btn-submit">
                            Login
                        </button>

                    </form>


                    <!-- REGISTER LINK -->
                    <p class="register-hint">
                        Don't have an account?
                        <a href="patient/register.php">Register here</a>
                    </p>

                </div>

            </div>

        </section>

    </div>

    <div id="resendModal" class="modal-overlay hidden">
        <div class="success-modal">

            <div class="success-icon">
                <i class="bx bxs-envelope"></i>
            </div>

            <h2>Verification Email Sent</h2>

            <p>
                A new verification link has been sent to your email.
                Please check your inbox to verify your account.
            </p>

            <button type="button" class="btn-success" onclick="closeResendModal()">
                OK
            </button>

        </div>
    </div>

    <!-- PASSWORD RESET SUCCESS MODAL -->
<div id="resetSuccessModal" class="modal-overlay hidden">

    <div class="success-modal">

        <div class="reset-success-icon">
            <i class="bx bx-check"></i>
        </div>

        <h2>Password Reset Successful</h2>

        <p>
            Your password has been successfully updated.
            You can now sign in using your new password.
        </p>

        <button type="button" class="btn-success" onclick="closeResetSuccessModal()">
            Continue to Login
        </button>

    </div>

</div>

    <script>
        function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }


    function resendVerification(event) {
        event.preventDefault();

        const email = document.getElementById("email").value;

        const form = document.createElement("form");
        form.method = "POST";
        form.action = "api/auth/resend_verification.php";

        const emailInput = document.createElement("input");
        emailInput.type = "hidden";
        emailInput.name = "email";
        emailInput.value = email;

        form.appendChild(emailInput);

        document.body.appendChild(form);
        form.submit();
    }


    function closeResendModal() {
        const modal = document.getElementById("resendModal");

        modal.classList.remove("show");

        setTimeout(() => {
            modal.classList.add("hidden");
        }, 250);

        // Remove ?resend=success from the URL
        window.history.replaceState({}, document.title, "login.php");
    }


    // CHECK URL FOR SUCCESS MODALS
    const urlParams = new URLSearchParams(window.location.search);


    // VERIFICATION EMAIL RESEND
    if (urlParams.get("resend") === "success") {

        const resendModal = document.getElementById("resendModal");

        resendModal.classList.remove("hidden");

        setTimeout(() => {
            resendModal.classList.add("show");
        }, 50);
    }


    // PASSWORD RESET SUCCESS
    if (urlParams.get("reset") === "success") {

        const resetSuccessModal = document.getElementById("resetSuccessModal");

        resetSuccessModal.classList.remove("hidden");

        setTimeout(() => {
            resetSuccessModal.classList.add("show");
        }, 50);
    }

    function closeResetSuccessModal() {

        const modal = document.getElementById("resetSuccessModal");

        modal.classList.remove("show");

        setTimeout(() => {
            modal.classList.add("hidden");
        }, 250);

        // Remove ?reset=success from the URL
        window.history.replaceState({}, document.title, "login.php");
    }
    </script>

</body>
</html>
