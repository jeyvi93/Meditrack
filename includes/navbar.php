<?php

/* Get notification badge count */
$userId = $_SESSION['user_id'] ?? 0;

$unreadCount = 0;


if ($userId) {

    require_once "../config/database.php";

    $unreadSql = "
        SELECT COUNT(*) AS unread_count
        FROM notifications
        WHERE user_id = ?
        AND is_viewed = 0
    ";

    $unreadStmt = mysqli_prepare($conn, $unreadSql);

    if ($unreadStmt) {

        mysqli_stmt_bind_param(
            $unreadStmt,
            "i",
            $userId
        );

        mysqli_stmt_execute($unreadStmt);

        $unreadResult = mysqli_stmt_get_result($unreadStmt);
        $unreadData = mysqli_fetch_assoc($unreadResult);

        $unreadCount = $unreadData['unread_count'] ?? 0;

        mysqli_stmt_close($unreadStmt);
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$full_name = $_SESSION['full_name'] ?? 'User';
?>

<!-- DESKTOP TOP NAVBAR -->
<script src="https://unpkg.com/lucide@latest"></script>
<nav class="navbar main-wrapper">
    <div class="logo">
        <img src="../assets/images/med.png" alt="MediTrack Logo">
    </div>

    <div class="desktop-menu">

        <a href="book_appointment.php"
           class="<?= $currentPage === 'book_appointment.php' ? 'active' : '' ?>">
            <i data-lucide="calendar-plus"></i>
            <span>Book</span>
        </a>

        <a href="view_appointments.php"
           class="<?= $currentPage === 'view_appointments.php' ? 'active' : '' ?>">
            <i data-lucide="calendar-days"></i>
            <span>Appointments</span>
        </a>

        <a href="notifications.php"
           class="<?= $currentPage === 'notifications.php' ? 'active' : '' ?>">

            <span class="nav-notification-icon">

                <i data-lucide="bell"></i>

                <?php if ($unreadCount > 0): ?>

                    <span class="notification-badge">
                        <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                    </span>

                <?php endif; ?>

            </span>

            <span>Notifications</span>

        </a>

        <!-- PROFILE DROPDOWN -->
        <div class="profile-dropdown">

            <button type="button"
                    class="profile-trigger"
                    onclick="toggleProfileDropdown()">

                <i data-lucide="circle-user-round"></i>

                <span><?= htmlspecialchars($full_name) ?></span>

                <i data-lucide="chevron-down" class="profile-arrow"></i>

            </button>

            <div id="profileDropdownMenu" class="profile-dropdown-menu">

                <a href="view_profile.php">
                    <i data-lucide="user-round"></i>
                    <span>View Profile</span>
                </a>

                <div class="dropdown-divider"></div>

                <button type="button" onclick="openLogoutModal()">
                    <i data-lucide="log-out"></i>
                    <span>Logout</span>
                </button>

            </div>

        </div>

    </div>
</nav>


<!-- MOBILE BOTTOM NAVIGATION -->
<nav class="bottom-nav">

    <a href="book_appointment.php"
       class="nav-item <?= $currentPage === 'book_appointment.php' ? 'active' : '' ?>">

        <i data-lucide="calendar-plus"></i>
        <span>Book</span>

    </a>


    <a href="view_appointments.php"
       class="nav-item <?= $currentPage === 'view_appointments.php' ? 'active' : '' ?>">

        <i data-lucide="calendar-days"></i>
        <span>Appointments</span>

    </a>


    <a href="notifications.php"
       class="nav-item <?= $currentPage === 'notifications.php' ? 'active' : '' ?>">

        <span class="nav-notification-icon">

            <i data-lucide="bell"></i>

            <?php if ($unreadCount > 0): ?>

                <span class="notification-badge">
                    <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                </span>

            <?php endif; ?>

        </span>

        <span>Notifications</span>

    </a>


    <a href="view_profile.php"
       class="nav-item <?= $currentPage === 'view_profile.php' ? 'active' : '' ?>">

        <i data-lucide="circle-user-round"></i>
        <span>Profile</span>

    </a>

</nav>


<!-- LOGOUT CONFIRMATION MODAL -->
<div id="logoutModal" class="modal-overlay hidden">

    <div class="modal-box logout-modal">

        <div class="message-icon warning">
            <i class='bx bx-log-out'></i>
        </div>

        <h3>Sign Out?</h3>

        <p>
            Are you sure you want to log out of your MediTrack account?
        </p>

        <div class="modal-btns">

            <button type="button"
                    class="btn-no"
                    onclick="closeLogoutModal()">
                Cancel
            </button>

            <button type="button"
                    class="btn-logout-confirm"
                    onclick="confirmLogout()">
                Logout
            </button>

        </div>

    </div>

</div>


<script>

function toggleProfileDropdown() {

    const dropdown = document.getElementById("profileDropdownMenu");

    dropdown.classList.toggle("show");
}


/* Close dropdown when clicking outside */

document.addEventListener("click", function(event) {

    const dropdown = document.querySelector(".profile-dropdown");
    const menu = document.getElementById("profileDropdownMenu");

    if (dropdown && !dropdown.contains(event.target)) {
        menu.classList.remove("show");
    }

});


/* Logout modal */

function openLogoutModal() {

    document.getElementById("profileDropdownMenu").classList.remove("show");

    document.getElementById("logoutModal").classList.remove("hidden");
}


function closeLogoutModal() {

    document.getElementById("logoutModal").classList.add("hidden");
}


function confirmLogout() {

    window.location.href = "../auth/logout.php";

}


document.addEventListener("DOMContentLoaded", function () {
    lucide.createIcons();

    // Automatically update notification badges
    async function updateNotificationBadge() {
        try {
            const response = await fetch("../patient/check_notifications.php", {
                cache: "no-store"
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (!data.success) {
                return;
            }

            document.querySelectorAll(".nav-notification-icon").forEach(icon => {
                let badge = icon.querySelector(".notification-badge");

                if (data.badge_count > 0) {
                    if (!badge) {
                        badge = document.createElement("span");
                        badge.className = "notification-badge";
                        icon.appendChild(badge);
                    }

                    badge.textContent = data.badge_count > 99
                        ? "99+"
                        : data.badge_count;
                } else if (badge) {
                    badge.remove();
                }
            });
        } catch (error) {
            console.error("Notification badge update failed:", error);
        }
    }

    updateNotificationBadge();
    setInterval(updateNotificationBadge, 5000);
});

</script>