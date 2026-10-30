<?php

// Detect which folder is currently using this sidebar.
$isAdminPage = basename(dirname($_SERVER['SCRIPT_NAME'])) === 'admin';

$adminPath = $isAdminPage ? '' : '../admin/';

$passwordPageUrl = $isAdminPage
    ? '../clinic/change_personnel_password.php'
    : 'change_personnel_password.php';

?>

<aside class="sidebar">

    <!-- LOGO -->
    <div class="logo">
        <img src="../assets/images/med.png" alt="Logo">
    </div>

    <!-- MENU -->
    <nav class="nav-menu">
        <p class="menu-label">Main Menu</p>

        <ul>
            <li>
                <a href="<?= $adminPath ?>accounts.php"
                   class="<?= basename($_SERVER['SCRIPT_NAME']) === 'accounts.php' ? 'active' : '' ?>">
                    <i class='bx bxs-user-detail'></i>
                    Account List
                </a>
            </li>

            <li>
                <a href="<?= $adminPath ?>create_personnel.php"
                   class="<?= basename($_SERVER['SCRIPT_NAME']) === 'create_personnel.php' ? 'active' : '' ?>">
                    <i class='bx bxs-user-plus'></i>
                    Create Personnel Account
                </a>
            </li>
        </ul>
    </nav>

    <!-- FOOTER -->
    <div class="sidebar-footer">

        <div class="admin-card" onclick="toggleLogoutMenu()">

            <img
                src="https://cdn-icons-png.flaticon.com/512/9131/9131590.png"
                alt="Administrator"
            >

            <div class="admin-info">
                <strong>
                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>
                </strong>

                <small>Administrator</small>
            </div>

            <i class='bx bx-chevron-up-down'></i>

        </div>

        <!-- DROPDOWN MENU -->
        <div id="logoutMenu" class="logout-menu">

            <!-- CHANGE PASSWORD -->
            <a href="<?= htmlspecialchars($passwordPageUrl, ENT_QUOTES, 'UTF-8') ?>">
                <i class='bx bx-lock-alt'></i>
                Change Password
            </a>

            <!-- LOGOUT -->
            <button type="button" onclick="openLogoutModal()">
                <i class='bx bx-log-out'></i>
                Logout
            </button>

        </div>

    </div>

</aside>


<!-- LOGOUT CONFIRMATION MODAL -->
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
                onclick="closeLogoutModal()">
                Cancel
            </button>

            <button
                type="button"
                class="logout-confirm"
                onclick="confirmLogout()">

                <i class='bx bx-log-out'></i>
                Logout

            </button>

        </div>

    </div>

</div>
