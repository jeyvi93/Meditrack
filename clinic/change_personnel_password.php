
<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$role = $_SESSION['role'] ?? '';

$allowedRoles = ['Administrator', 'School Nurse', 'Clinic Staff'];

if (!in_array($role, $allowedRoles, true)) {
    http_response_code(403);
    exit('Access denied.');
}

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';

unset($_SESSION['success'], $_SESSION['error']);

$isStaff = in_array($role, ['School Nurse', 'Clinic Staff'], true);

$backUrl = $role === 'Administrator'
    ? '../admin/accounts.php'
    : 'dashboard.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Change Password | MediTrack</title>

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/staffs.css">
    <link rel="stylesheet" href="../assets/css/change_personnel_password.css">

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<?php if ($role === 'Administrator'): ?>

    <?php include "../includes/admin_sidebar.php"; ?>

<?php else: ?>

    <?php include "../includes/sidebar.php"; ?>

<?php endif; ?>

<main class="main-content">

    <header class="module-header">
        <div class="title-area">
            <h1>Change Password</h1>
            <p>Update your MediTrack account password.</p>
        </div>
    </header>

    <div class="password-layout">

        <!-- LEFT: CHANGE PASSWORD FORM -->
        <section class="personnel-password-card">

            <div class="password-heading">
                <div class="password-heading-icon">
                    <i class="bx bx-lock-alt"></i>
                </div>

                <div>
                    <h2>Account Security</h2>
                    <p>Enter your password details below.</p>
                </div>
            </div>

            <form
                action="../actions/change_personnel_password.php"
                method="POST"
                class="personnel-password-form"
                novalidate
            >

                <div class="form-group">
                    <label for="current_password">
                        <?= $isStaff ? 'Temporary / Current Password' : 'Current Password' ?>
                    </label>

                    <div class="password-input-box">
                        <i class="bx bx-key"></i>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            placeholder="<?= $isStaff
                                ? 'Enter temporary or current password'
                                : 'Enter current password' ?>"
                            autocomplete="current-password"
                            required
                        >

                        <i
                            class="bx bx-show password-toggle"
                            onclick="togglePassword('current_password', this)"
                        ></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>

                    <div class="password-input-box">
                        <i class="bx bx-lock-alt"></i>

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="Enter new password"
                            autocomplete="new-password"
                            required
                        >

                        <i
                            class="bx bx-show password-toggle"
                            onclick="togglePassword('new_password', this)"
                        ></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>

                    <div class="password-input-box">
                        <i class="bx bx-shield-alt-2"></i>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Re-enter new password"
                            autocomplete="new-password"
                            required
                        >

                        <i
                            class="bx bx-show password-toggle"
                            onclick="togglePassword('confirm_password', this)"
                        ></i>
                    </div>

                    <?php if ($errorMessage !== ''): ?>
                        <div class="password-error-message">
                            <i class="bx bx-error-circle"></i>
                            <span>
                                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="password-actions">

                    <a
                        href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>"
                        class="password-cancel"
                    >
                        Cancel
                    </a>

                    <button type="submit" class="password-save">
                        Save New Password
                    </button>

                </div>

            </form>

        </section>


        <!-- RIGHT: SECURITY REMINDER -->
        <aside class="password-note">

            <div class="password-note-icon">
                <i class="bx bx-shield-quarter"></i>
            </div>

            <span class="password-note-label">SECURITY REMINDER</span>

            <h2>Keep your account secure</h2>

            <p class="password-note-description">
                Your password protects your personal and clinic account information.
                Follow these reminders when updating it.
            </p>

            <div class="password-note-list">

                <div class="password-note-item">
                    <i class="bx bx-check-circle"></i>

                    <div>
                        <strong>Use a strong password</strong>
                        <p>
                            Choose at least 8 characters that are difficult to guess.
                        </p>
                    </div>
                </div>

                <div class="password-note-item">
                    <i class="bx bx-check-circle"></i>

                    <div>
                        <strong>Keep it private</strong>
                        <p>
                            Never share your password with other people.
                        </p>
                    </div>
                </div>

                <div class="password-note-item">
                    <i class="bx bx-check-circle"></i>

                    <div>
                        <strong>Confirm your new password</strong>
                        <p>
                            Make sure both new password fields match.
                        </p>
                    </div>
                </div>

            </div>

            <div class="password-note-footer">
                <i class="bx bx-info-circle"></i>

                <p>
                    <?= $isStaff
                        ? 'Use the temporary password provided to you if you have not changed it yet.'
                        : 'Your current password is required to verify your identity.' ?>
                </p>
            </div>

        </aside>

    </div>

</main>


<?php if ($successMessage !== ''): ?>

<div id="passwordSuccessModal" class="modal active">
    <div class="modal-content message-modal">

        <div class="personnel-password-success-icon">
            <i class="bx bx-check"></i>
        </div>

        <h2>Password Updated!</h2>

        <p>
            <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <div class="modal-actions">
            <button
                type="button"
                class="btn-primary"
                onclick="window.location.href='dashboard.php'"
            >
                Continue
            </button>
        </div>

    </div>
</div>

<?php endif; ?>


<script src="../assets/js/accounts.js"></script>

</body>
</html>
