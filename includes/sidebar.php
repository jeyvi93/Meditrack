<?php
require_once "../config/database.php";

$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
$pendingAppointmentCount = 0;

$pendingSql = "
    SELECT COUNT(*) AS pending_count
    FROM appointments
    WHERE status = 'Pending'
";

$pendingStmt = mysqli_prepare($conn, $pendingSql);

if ($pendingStmt) {
    mysqli_stmt_execute($pendingStmt);

    $pendingResult = mysqli_stmt_get_result($pendingStmt);
    $pendingData = mysqli_fetch_assoc($pendingResult);

    $pendingAppointmentCount = $pendingData['pending_count'] ?? 0;

    mysqli_stmt_close($pendingStmt);
}
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
                <a href="appointments.php" class="appointment-link">
                    <i class='bx bxs-calendar-check'></i>
                    <span>Appointments</span>

                    <?php if ($pendingAppointmentCount > 0): ?>
                        <span class="appointment-badge">
                            <?= $pendingAppointmentCount > 99 ? '99+' : $pendingAppointmentCount ?>
                        </span>
                    <?php endif; ?>
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

            <?php if ($role !== 'Clinic Staff'): ?>
                <li>
                    <a href="referral_form.php">
                        <i class='bx bxs-share-alt'></i> Referrals
                    </a>
                </li>

                <li>
                    <a href="archived_records.php"
                    class="<?= $currentPage === 'archived_records.php' ? 'active' : '' ?>">
                        <i class='bx bx-archive'></i> Archive
                    </a>
                </li>
            <?php endif; ?>
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    async function updateAppointmentBadge() {
        try {
            const response = await fetch("check_appointments.php", {
                cache: "no-store"
            });

            if (!response.ok) return;

            const data = await response.json();

            if (!data.success) return;

            const appointmentLink = document.querySelector(".appointment-link");
            if (!appointmentLink) return;

            let badge = appointmentLink.querySelector(".appointment-badge");

            if (data.pending_count > 0) {
                if (!badge) {
                    badge = document.createElement("span");
                    badge.className = "appointment-badge";
                    appointmentLink.appendChild(badge);
                }

                badge.textContent = data.pending_count > 99
                    ? "99+"
                    : data.pending_count;
            } else if (badge) {
                badge.remove();
            }
        } catch (error) {
            console.error("Appointment badge update failed:", error);
        }
    }

    updateAppointmentBadge();
    setInterval(updateAppointmentBadge, 5000);
});
</script>