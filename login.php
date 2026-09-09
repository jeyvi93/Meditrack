<?php
session_start();

$error = $_GET['error'] ?? '';
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

                            <div class="input-box">

                                <i class="fas fa-lock"></i>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                >

                            </div>
                        </div>

                        <div class="login-error-container">
                            <?php if ($error === '1'): ?>
                                <span class="login-error">
                                    <i class="fas fa-circle-exclamation"></i>
                                    Invalid email or password.
                                </span>
                            <?php elseif ($error === 'invalid_role'): ?>
                                <span class="login-error">
                                    <i class="fas fa-circle-exclamation"></i>
                                    Your account has an invalid role. Please contact the clinic administrator.
                                </span>
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

</body>
</html>
