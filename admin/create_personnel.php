<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin-only access
if (($_SESSION['role'] ?? '') !== 'Administrator') {
    http_response_code(403);
    exit('Access denied. Admin privileges are required.');
}

require_once "../config/database.php";

// Retrieve messages from the backend
$errorMessage = $_SESSION['error'] ?? '';
$errorField = $_SESSION['error_field'] ?? '';
$successMessage = $_SESSION['success'] ?? '';

// Retrieve old form values
$oldStaffIdNumber = $_SESSION['old_staff_id_number'] ?? '';
$oldRole = $_SESSION['old_role'] ?? '';
$oldLastName = $_SESSION['old_last_name'] ?? '';
$oldFirstName = $_SESSION['old_first_name'] ?? '';
$oldMiddleName = $_SESSION['old_middle_name'] ?? '';
$oldEmail = $_SESSION['old_email'] ?? '';

// Clear flash messages and stored values after copying them
unset(
    $_SESSION['error'],
    $_SESSION['error_field'],
    $_SESSION['success'],
    $_SESSION['old_staff_id_number'],
    $_SESSION['old_role'],
    $_SESSION['old_last_name'],
    $_SESSION['old_first_name'],
    $_SESSION['old_middle_name'],
    $_SESSION['old_email']
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Personnel Account | MediTrack</title>

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/create_personnel.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>

<!-- Admin Sidebar -->
<aside class="sidebar">

    <div class="logo">
        <img src="../assets/images/med.png" alt="Logo">
    </div>

    <nav class="nav-menu">
        <p class="menu-label">Main Menu</p>

        <ul>
            <li>
                <a href="accounts.php">
                    <i class='bx bxs-user-detail'></i>
                    Account List
                </a>
            </li>

            <li>
                <a href="create_personnel.php" class="active">
                    <i class='bx bxs-user-plus'></i>
                    Create Personnel Account
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">

        <div class="admin-card" onclick="toggleLogoutMenu()">
            <img
                src="https://cdn-icons-png.flaticon.com/512/9131/9131590.png"
                alt="Administrator"
            >

            <div class="admin-info">
                <strong>
                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?>
                </strong>

                <small>Administrator</small>
            </div>

            <i class='bx bx-chevron-up-down'></i>
        </div>

        <div id="logoutMenu" class="logout-menu">
            <button type="button" onclick="openLogoutModal()">
                <i class='bx bx-log-out'></i>
                Logout
            </button>
        </div>

    </div>

</aside>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal-overlay">
    <div class="logout-modal">

        <div class="logout-icon">
            <i class='bx bx-log-out-circle'></i>
        </div>

        <h2>Sign Out</h2>

        <p>
            Are you sure you want to sign out of your MediTrack account?
        </p>

        <div class="logout-buttons">
            <button
                type="button"
                class="logout-cancel"
                onclick="closeLogoutModal()"
            >
                Cancel
            </button>

            <button
                type="button"
                class="logout-confirm"
                onclick="confirmLogout()"
            >
                <i class='bx bx-log-out'></i>
                Logout
            </button>
        </div>

    </div>
</div>

<?php if ($successMessage !== ''): ?>
    <div
        id="personnelSuccessModal"
        class="modal-overlay personnel-success-overlay"
        style="display: flex;"
    >
        <div class="personnel-success-modal">
            <div class="personnel-success-icon">
                <i class='bx bx-check-circle'></i>
            </div>

            <h2>Account Created Successfully!</h2>

            <p>
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="personnel-success-buttons">
                <button
                    type="button"
                    class="personnel-success-continue"
                    onclick="closePersonnelSuccessModal()"
                >
                    Continue
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content -->
<main class="main-content">

    <header class="page-header">
        <div>
            <h1>Create Personnel Account</h1>
            <p>Create login accounts for existing clinic personnel.</p>
        </div>
    </header>

    <section class="personnel-card">

        <div class="form-heading">
            <h2>Personnel Information</h2>
            <p>Enter the ID of an existing clinic staff member to create their account.</p>
        </div>

        <?php if ($errorMessage !== '' && $errorField === ''): ?>
            <p class="form-message form-error">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form action="../actions/create_personnel_process.php" method="POST">

            <div class="form-row">

                <div class="form-group">
                    <label for="staff_id_number">Personnel ID Number</label>
                    <input
                        type="text"
                        id="staff_id_number"
                        name="staff_id_number"
                        placeholder="Enter registered personnel ID"
                        value="<?= htmlspecialchars($oldStaffIdNumber, ENT_QUOTES, 'UTF-8') ?>"
                        oninput="this.value = this.value.trimStart()"
                        required
                    >
                    <span class="field-error" id="staff_id_error">
                        <?= $errorField === 'staff_id_number' ? htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') : '' ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="role">Personnel Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select personnel role</option>
                        <option value="School Nurse" <?= $oldRole === 'School Nurse' ? 'selected' : '' ?>>
                            School Nurse
                        </option>
                        <option value="Clinic Staff" <?= $oldRole === 'Clinic Staff' ? 'selected' : '' ?>>
                            Clinic Staff
                        </option>
                    </select>

                    <span class="field-error" id="role_error">
                        <?= $errorField === 'role' ? htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') : '' ?>
                    </span>
                </div>

            </div>

            <div class="form-row name-row">

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        placeholder="Enter last name"
                        value="<?= htmlspecialchars($oldLastName, ENT_QUOTES, 'UTF-8') ?>"
                        oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                        required
                    >
                    <span class="field-error" id="last_name_error">
                        <?= $errorField === 'last_name' ? htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') : '' ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        placeholder="Enter first name"
                        value="<?= htmlspecialchars($oldFirstName, ENT_QUOTES, 'UTF-8') ?>"
                        oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                        required
                    >
                    <span class="field-error" id="first_name_error">
                        <?= $errorField === 'first_name' ? htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') : '' ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="middle_name">
                        Middle Name <span class="middle-name-note">(Leave blank if none)</span>
                    </label>
                    <input
                        type="text"
                        id="middle_name"
                        name="middle_name"
                        placeholder="Enter middle name"
                        value="<?= htmlspecialchars($oldMiddleName, ENT_QUOTES, 'UTF-8') ?>"
                        oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                    >
                    <span class="field-error" id="middle_name_error">
                        <?= $errorField === 'middle_name' ? htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') : '' ?>
                    </span>
                </div>

            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter personnel email"
                    value="<?= htmlspecialchars($oldEmail, ENT_QUOTES, 'UTF-8') ?>"
                    oninput="this.value = this.value.trim()"
                    required
                >
                <span class="field-error" id="email_error">
                    <?= $errorField === 'email' ? htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') : '' ?>
                </span>
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="password-box">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create password"
                            required
                        >
                        <i
                            class="bx bx-show password-toggle"
                            onclick="togglePassword('password', this)"
                        ></i>
                    </div>

                    <span class="field-error" id="password_error">
                        <?= $errorField === 'password' ? htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') : '' ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-box">
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Re-enter password"
                            required
                        >
                        <i
                            class="bx bx-show password-toggle"
                            onclick="togglePassword('confirm_password', this)"
                        ></i>
                    </div>
                    <span class="field-error" id="confirm_password_error">
                        <?= $errorField === 'confirm_password' ? htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') : '' ?>
                    </span>
                </div>

            </div>

            <div class="form-actions">
                <a href="accounts.php" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    Create Account
                </button>
            </div>

        </form>

    </section>

</main>

<script src="../assets/js/accounts.js"></script>


</body>
</html>