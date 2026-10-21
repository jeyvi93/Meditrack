<?php

session_start();

require_once "../config/database.php";

$userId = $_SESSION['user_id'] ?? 0;

$user = null;

if ($userId) {

    $sql = "
        SELECT 
            u.full_name,
            u.email,
            u.role,
            s.student_id_number,
            st.staff_id_number
        FROM users u
        LEFT JOIN students s ON u.student_id = s.id
        LEFT JOIN staffs st ON u.staff_id = st.id
        WHERE u.id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $userId);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
    }
}

if (!$user) {
    header("Location: ../login.php");
    exit();
}

$fullName = $user['full_name'];
$email = $user['email'];
$role = $user['role'];

$idNumber = $user['student_id_number']
    ?? $user['staff_id_number']
    ?? 'N/A';

$initials = '';

$nameParts = explode(' ', trim($fullName));

foreach ($nameParts as $part) {

    if (!empty($part)) {
        $initials .= strtoupper(substr($part, 0, 1));
    }

}

$initials = substr($initials, 0, 2);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | MediTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/view_profile.css">
</head>
<body>

<div class="bg-gradient"></div>

<?php include '../includes/navbar.php'; ?>


<!-- MOBILE TOP BAR (Title only, Logout moved to card) -->
<nav class="mobile-top-bar">
    <span class="nav-title">Profile</span>
</nav>

<main class="main-wrapper">
    <section class="booking-grid">
        
        <!-- Left Side: Sidebar (Hidden on Mobile) -->
        <div class="info-side">
            <div class="status-card">
                <div class="status-icon"><i class='bx bxs-user-circle'></i></div>
                <div>
                    <h4>Profile Verified</h4>
                    <p>Your account information is secured.</p>
                </div>
            </div>
            
            <div class="reminder-list">
                <h5>Profile Settings:</h5>
                <ul>
                    <li><i class='bx bx-check-double'></i> Keep your email up to date.</li>
                    <li><i class='bx bx-check-double'></i> ID numbers cannot be changed.</li>
                    <li><i class='bx bx-check-double'></i> Contact clinic for role updates.</li>
                </ul>
            </div>
        </div>

        <!-- Right Side: Profile Details Card -->
        <div class="appointment-card">
            <div class="profile-header-inner">
                <div class="avatar-circle">
                    <i class='bx bxs-user-circle'></i>
                </div>
                <div class="header-text">
                    <h2><?= htmlspecialchars($fullName) ?></h2>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-group">
                    <label>Full Name</label>
                    <div class="info-value">
                        <i class='bx bx-user'></i>
                        <span><?= htmlspecialchars($fullName) ?></span>
                    </div>
                </div>

                <div class="info-group">
                    <label>Email Address</label>
                    <div class="info-value">
                        <i class='bx bx-envelope'></i>
                        <span><?= htmlspecialchars($email) ?></span>
                    </div>
                </div>

                <div class="info-group">
                    <label>ID Number</label>
                    <div class="info-value">
                        <i class='bx bx-id-card'></i>
                        <span><?= htmlspecialchars($idNumber) ?></span>
                    </div>
                </div>

                <div class="info-group">
                    <label>Academic Role</label>
                    <div class="info-value">
                        <i class='bx bx-briefcase'></i>
                        <span><?= htmlspecialchars(ucfirst($role)) ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <button class="btn-edit-profile" onclick="window.location.href='edit_profile.php'">
                    <i class='bx bx-edit-alt'></i> Edit Profile Details
                </button>

                <!-- NEW: Logout Button (Only visible in Mobile via CSS) -->
                <button class="btn-logout-mobile" onclick="openLogoutModal()">
                    <i class='bx bx-log-out'></i> Logout Account
                </button>
            </div>
        </div>
    </section>
</main>


<script>
function openLogoutModal() { document.getElementById('logoutModal').classList.remove('hidden'); }
    function closeLogoutModal() { document.getElementById('logoutModal').classList.add('hidden'); }
    function confirmLogout() {window.location.href = "../auth/logout.php";}
</script>

</body>
</html>