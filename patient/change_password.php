<?php

session_start();

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';

unset($_SESSION['success']);
unset($_SESSION['error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | MediTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/change_password.css">
</head>
<body>

<div class="bg-gradient"></div>

<?php include '../includes/navbar.php'; ?>

<!-- MOBILE TOP BAR (Back button left end) -->
<nav class="mobile-top-bar">
    <a href="edit_profile.php" class="back-btn-mobile">
        <i class='bx bx-chevron-left'></i>
    </a>
    <span class="nav-title">Security</span>
    <div class="spacer-nav"></div>
</nav>

<main class="main-wrapper">
    <section class="booking-grid">
        
        <!-- Left Side: Sidebar (Hidden on Mobile) -->
        <div class="info-side">
            <div class="status-card">
                <div class="status-icon"><i class='bx bxs-lock-alt'></i></div>
                <div>
                    <h4>Security First</h4>
                    <p>Protect your account with a strong password.</p>
                </div>
            </div>
            
            <div class="reminder-list">
                <h5>Password Tips:</h5>
                <ul>
                    <li><i class='bx bx-check-shield'></i> Use at least 8 characters.</li>
                    <li><i class='bx bx-check-shield'></i> Mix letters, numbers, and symbols.</li>
                    <li><i class='bx bx-check-shield'></i> Avoid using your school ID as password.</li>
                </ul>
            </div>
        </div>

        <!-- Right Side: Change Password Card -->
        <div class="appointment-card">
            <div class="profile-header-inner">
                <div class="icon-circle-large"><i class='bx bx-lock-open-alt'></i></div>
                <div class="header-text">
                    <h2>Update Password</h2>
                    <p>Enter your current and new credentials below.</p>
                </div>
            </div>

            <form action="../actions/change_password.php" method="POST" class="password-form">
                <div class="info-grid single-col">

                    <?php if ($errorMessage): ?>
                        <div class="password-error-message">
                            <i class='bx bx-error-circle'></i>
                            <span><?= htmlspecialchars($errorMessage) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-group">
                        <label>Current Password</label>
                        <div class="input-box">
                            <i class='bx bx-key'></i>
                            <input type="password"
                                name="current_password"
                                placeholder="••••••••"
                                required>
                        </div>
                    </div>

                    <div class="info-group">
                        <label>New Password</label>
                        <div class="input-box">
                            <i class='bx bx-lock-alt'></i>
                            <input type="password"
                                name="new_password"
                                placeholder="Enter new password"
                                required>
                        </div>
                    </div>

                    <div class="info-group">
                        <label>Confirm New Password</label>
                        <div class="input-box">
                            <i class='bx bx-shield-alt-2'></i>
                            <input type="password"
                                name="confirm_password"
                                placeholder="Repeat new password"
                                required>
                        </div>
                    </div>

                </div>

                <div class="profile-actions">
                    <button type="submit" class="btn-save-profile">
                        <i class='bx bx-save'></i> Save New Password
                    </button>
            
                </div>
            </form>
        </div>
    </section>
</main>


<!-- PASSWORD SUCCESS MODAL -->
<div id="passwordSuccessModal" class="modal-overlay hidden">
    <div class="modal-box password-success-box">

        <div class="password-success-icon">
            <i class='bx bx-check'></i>
        </div>

        <h3>Password Updated!</h3>

        <p>
            <?= htmlspecialchars($successMessage) ?>
        </p>

        <button type="button"
                class="btn-success-password"
                onclick="closePasswordSuccessModal()">
            Continue
        </button>

    </div>
</div>

<script>
<?php if ($successMessage): ?>
    document.getElementById('passwordSuccessModal').classList.remove('hidden');
<?php endif; ?>
</script>

<script src="../assets/js/change_password.js"></script>


</body>
</html>