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
                <a href="dashboard.php">
                    <i class='bx bxs-dashboard'></i> Dashboard
                </a>
            </li>

            <li>
                <a href="students.php">
                    <i class='bx bxs-user-detail'></i> Students
                </a>
            </li>

            <li>
                <a href="staffs.php">
                    <i class='bx bxs-user-detail'></i> School Staffs
                </a>
            </li>

            <li>
                <a href="appointments.php">
                    <i class='bx bxs-calendar-check'></i> Appointments
                </a>
            </li>

            <li>
                <a href="medicine.php">
                    <i class='bx bxs-capsule'></i> Medicine
                </a>
            </li>

            <li>
                <a href="consultation.php">
                    <i class='bx bxs-virus'></i> Consultation Logs
                </a>
            </li>

            <li>
                <a href="reports.php">
                    <i class='bx bxs-file'></i> Monthly Reports
                </a>
            </li>

            <li>
                <a href="referral-form.php">
                    <i class='bx bxs-share-alt'></i> Referrals
                </a>
            </li>
        </ul>
    </nav>

    <!-- FOOTER -->
    <div class="sidebar-footer">

        <div class="admin-card" onclick="toggleLogoutMenu()">

            <img src="https://cdn-icons-png.flaticon.com/512/9131/9131590.png" alt="Admin">

            <div class="admin-info">
                <strong>
                    <?php echo $_SESSION['full_name']; ?>
                </strong>

                <small>
                    <?php echo $_SESSION['role']; ?>
                </small>
            </div>

            <i class='bx bx-chevron-up-down'></i>

        </div>

        <!-- LOGOUT MENU -->
        <div id="logoutMenu" class="logout-menu">
            <button onclick="openLogoutModal()">
                <i class='bx bx-log-out'></i> Logout
            </button>
        </div>

    </div>

</aside>

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

            <button class="logout-cancel" onclick="closeLogoutModal()">
                Cancel
            </button>

            <button class="logout-confirm" onclick="confirmLogout()">
                <i class='bx bx-log-out'></i>
                Logout
            </button>

        </div>

    </div>

</div>