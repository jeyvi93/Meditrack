<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$full_name = $_SESSION['full_name'] ?? 'User';
?>

<!-- DESKTOP TOP NAVBAR -->
<nav class="navbar main-wrapper">
    <div class="logo">
        <img src="../assets/images/med.png" alt="MediTrack Logo">
    </div>

    <div class="desktop-menu">

        <a href="book_appointment.php"
           class="<?= $currentPage === 'book_appointment.php' ? 'active' : '' ?>">
            <i class='bx bx-calendar-plus'></i>
            <span>Book Appointment</span>
        </a>

        <a href="view_appointments.php"
           class="<?= $currentPage === 'view_appointments.php' ? 'active' : '' ?>">
            <i class='bx bx-calendar-event'></i>
            <span>My Appointments</span>
        </a>

        <a href="notifications.php"
           class="<?= $currentPage === 'notifications.php' ? 'active' : '' ?>">
            <i class='bx bx-bell'></i>
            <span>Notifications</span>
        </a>

        <!-- PROFILE DROPDOWN -->
        <div class="profile-dropdown">

            <button type="button"
                    class="profile-trigger"
                    onclick="toggleProfileDropdown()">

                <i class='bx bx-user-circle'></i>

                <span><?= htmlspecialchars($full_name) ?></span>

                <i class='bx bx-chevron-down profile-arrow'></i>
            </button>

            <div id="profileDropdownMenu" class="profile-dropdown-menu">

                <a href="view_profile.php">
                    <i class='bx bx-user'></i>
                    <span>View Profile</span>
                </a>

                <div class="dropdown-divider"></div>

                <button type="button" onclick="openLogoutModal()">
                    <i class='bx bx-log-out'></i>
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
        <i class='bx bx-calendar-plus'></i>
        <span>Book</span>
    </a>

    <a href="view_appointments.php"
       class="nav-item <?= $currentPage === 'view_appointments.php' ? 'active' : '' ?>">
        <i class='bx bx-calendar-event'></i>
        <span>Appointments</span>
    </a>

    <a href="notifications.php"
       class="nav-item <?= $currentPage === 'notifications.php' ? 'active' : '' ?>">
        <i class='bx bx-bell'></i>
        <span>Notifications</span>
    </a>

    <a href="view_profile.php"
       class="nav-item <?= $currentPage === 'view_profile.php' ? 'active' : '' ?>">
        <i class='bx bx-user-circle'></i>
        <span>Profile</span>
    </a>

</nav>


<!-- LOGOUT CONFIRMATION MODAL -->
<div id="logoutModal" class="modal-overlay hidden">

    <div class="modal-box logout-modal">

        <i class='bx bx-log-out-circle logout-icon'></i>

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
</script>