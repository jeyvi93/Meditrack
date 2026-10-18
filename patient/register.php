<?php
session_start();

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
$errorField = $_SESSION['error_field'] ?? '';
$oldFullName = $_SESSION['old_full_name'] ?? '';
$oldEmail = $_SESSION['old_email'] ?? '';
$oldRole = $_SESSION['old_role'] ?? '';
$oldIdNumber = $_SESSION['old_id_number'] ?? '';

unset($_SESSION['old_full_name']);
unset($_SESSION['old_email']);
unset($_SESSION['old_role']);
unset($_SESSION['old_id_number']);

unset($_SESSION['success']);
unset($_SESSION['error']);
unset($_SESSION['error_field']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | MediTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/register.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-gradient"></div>

    <div class="main-wrapper">
        <!-- EXACT NAVBAR FROM HOME PAGE -->
        <nav class="navbar">
            <div class="logo">
                <img src="../assets/images/med.png" alt="MediTrack Logo">
            </div>
            <div class="nav-auth">
                <a href="../index.php" class="home-link">Home</a>
                <a href="../login.php" class="btn-login">Login</a>
            </div>
        </nav>

        <section class="register-section">
            <div class="register-card">
                
                <!-- Left Side: Decorative Image (Hidden on Mobile) -->
                <div class="register-info">
                <div class="info-content">
                    <span class="badge">Join MediTrack</span>
                    <h2>Fast-track your clinic appointments.</h2>
                    <p>Creating an account allows you to book appointments, view appoinment status, and receive clinic notifications.</p>
                    
                    <ul class="benefit-list">
                        <li><i class="fa-solid fa-circle-check"></i> Online Appointment Booking</li>
                        <li><i class="fa-solid fa-circle-check"></i> Real-time Appointment Status</li>
                        <li><i class="fa-solid fa-circle-check"></i> Secure Data Privacy</li>
                    </ul>
                </div>
            </div>

                <!-- Right Side: The Form -->
                <div class="register-form-container">
                    <div class="form-header">
                        <h1>Create Account</h1>
                        <p>Fill in your details to get started.</p>
                    </div>

                    <form action="../actions/register_process.php" method="POST" class="registration-form">
                        <div class="form-grid">
                            <div class="input-group full-width">
                                <label>Full Name</label>
                                <div class="input-box">
                                    <i class="fas fa-user"></i>
                                    <input type="text" name="full_name" placeholder="Enter full name"
                                    value="<?php echo htmlspecialchars($oldFullName); ?>"
                                    pattern="[A-Za-zÀ-ÿ .'-]+"
                                    title="Name must contain letters only"
                                    required>
                                </div>
                                <?php if ($errorField === 'full_name'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group full-width">
                                <label>Email Address</label>
                                <div class="input-box">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email"  name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($oldEmail); ?>" required>
                                </div>
                                <?php if ($errorField === 'email'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label>Role</label>
                                <div class="input-box">
                                    <i class="fas fa-id-badge"></i>
                                    <select name="role" required>
                                        <option value="" disabled <?php echo $oldRole === '' ? 'selected' : ''; ?>>Select Role</option>
                                        <option value="Student" <?php echo $oldRole === 'Student' ? 'selected' : ''; ?>>Student</option>
                                        <option value="Teaching Staff" <?php echo $oldRole === 'Teaching Staff' ? 'selected' : ''; ?>>Teaching Staff</option>
                                        <option value="Non-Teaching Staff" <?php echo $oldRole === 'Non-Teaching Staff' ? 'selected' : ''; ?>>Non-Teaching Staff</option>
                                                                            </select>
                                </div>
                                <?php if ($errorField === 'role'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label>ID Number</label>
                                <div class="input-box">
                                    <i class="fas fa-id-card"></i>
                                    <input type="text"
                                    name="id_number"
                                    placeholder="Enter ID Number"
                                    maxlength="20"
                                    pattern="[0-9!@#$%^&*()_+=\-./ ]+"
                                    oninput="this.value = this.value.replace(/[A-Za-z]/g, '')"
                                    title="ID number can contain numbers and symbols only."
                                    value="<?php echo htmlspecialchars($oldIdNumber); ?>"
                                    required>
                                </div>
                                <?php if ($errorField === 'id_number'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label>Password</label>
                                <div class="input-box password-box">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="password" id="password" placeholder="Enter Password" required>
                                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
                                </div>
                                <?php if ($errorField === 'password'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label>Confirm Password</label>
                                <div class="input-box password-box">
                                    <i class="fas fa-shield-halved"></i>
                                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter Password" required>
                                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                                </div>
                                <?php if ($errorField === 'confirm_password'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Complete Registration</button>
                    </form>

                    <p class="mobile-login-hint">Already have an account? <a href="../login.php">Login here</a></p>
                </div>
            </div>
        </section>
    </div>

    <!-- SUCCESS MODAL -->
        <div id="successModal" class="modal-overlay hidden">
            <div class="success-modal">

                <div class="success-icon">
                    <i class="bx bxs-envelope"></i>
                </div>

                <h2>Check Your Email</h2>

                <p>
                    A confirmation link has been sent to your email.
                    Please check your inbox to verify your account.
                </p>

                <button class="btn-success" onclick="goToLogin()">
                    OK
                </button>

            </div>
        </div>

    <script>

        const successModal = document.getElementById("successModal");


        function goToLogin() {
            window.location.href = "../login.php";
        }


        <?php if (!empty($successMessage)): ?>

        successModal.classList.remove("hidden");

        setTimeout(() => {
            successModal.classList.add("show");
        }, 50);

        <?php endif; ?>

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

    </script>
</body>
</html>