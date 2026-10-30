<?php

session_start();

require_once "../config/database.php";

if (($_SESSION['role'] ?? '') === 'Administrator') {
    header("Location: ../admin/accounts.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'Clinic Staff') {
    header("Location: ../clinic/dashboard.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'School Nurse') {
    header("Location: ../clinic/dashboard.php");
    exit();
}

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

$idNumber = $user['student_id_number']
    ?? $user['staff_id_number']
    ?? 'N/A';

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
    <title>Edit Profile | MediTrack</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/edit_profile.css">
</head>
<body>

<div class="bg-gradient"></div>

<?php include '../includes/navbar.php'; ?>

<!-- MOBILE TOP BAR -->
        <nav class="mobile-top-bar">
            <a href="view_profile.php" class="back-btn-mobile">
                <i class='bx bx-chevron-left'></i>
            </a>
            <span class="nav-title">Edit Profile</span>
            <div class="spacer-nav"></div>
        </nav>

<main class="main-wrapper">
    <section class="booking-grid">
        

        <!-- SIDEBAR (Visible on Web, Hidden on Mobile) -->
        <div class="info-side">
            <div class="status-card">
                <div class="status-icon"><i class='bx bxs-edit-alt'></i></div>
                <div>
                    <h4>Account Update</h4>
                    <p>Keep your contact details verified.</p>
                </div>
            </div>
            
            <div class="reminder-list">
                <h5>Guidelines:</h5>
                <ul>
                    <li><i class='bx bx-check-double'></i> Use active email address.</li>
                    <li><i class='bx bx-check-double'></i> Cannot modify ID Number.</li>
                    <li><i class='bx bx-check-double'></i> Save changes before leaving.</li>
                </ul>
            </div>
        </div>

        <!-- EDIT FORM CARD -->
        <div class="appointment-card">
            <div class="profile-header-inner">
                <div class="avatar-circle">
                    <i class='bx bxs-user-circle'></i>
                </div>
                <div class="header-text">
                    <h2>Update Information</h2>
                    <p>Personalize your clinic profile details.</p>
                </div>
            </div>

            <form action="../actions/update_profile.php"
            method="POST"
            class="edit-form">
                <div class="info-grid">
                    <div class="info-group">
                        <label>Full Name</label>
                        <div class="input-box disabled">
                            <i class='bx bx-user'></i>
                            <input type="text"
                                name="full_name"
                                value="<?= htmlspecialchars($fullName) ?>"
                                disabled>
                        </div>
                    </div>

                    <div class="info-group">
                        <label>Email Address</label>
                        <div class="input-box">
                            <i class='bx bx-envelope'></i>
                            <input type="email"
                                name="email"
                                value="<?= htmlspecialchars($email) ?>"
                                required>
                        </div>
                    </div>

                    <div class="info-group">
                        <label>ID Number</label>
                        <div class="input-box disabled">
                            <i class='bx bx-id-card'></i>
                            <input type="text"
                                value="<?= htmlspecialchars($idNumber) ?>"
                                disabled>
                        </div>
                    </div>

                    <div class="info-group">
                        <label>Password</label>
                        <div class="input-box space-between">
                            <div class="flex-align">
                                <i class='bx bx-lock-alt'></i>
                                <span>••••••••</span>
                            </div>
                            <button type="button" class="btn-text-link" onclick="location.href='change_password.php'">Change</button>
                        </div>
                    </div>
                </div>

                

                <div class="profile-actions">
                    <button type="submit" class="btn-save-profile">
                        <i class='bx bx-check-circle'></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>


<!-- PROFILE UPDATE SUCCESS MODAL -->
<div id="successModal" class="modal-overlay hidden">
    <div class="modal-box success-modal-box">

        <i class='bx bx-check-circle success-icon'></i>

        <h3>Profile Updated!</h3>

        <p>
            <?= htmlspecialchars($successMessage) ?>
        </p>

        <button type="button" class="btn-success" onclick="window.location.href='view_profile.php'">
            Done
        </button>

    </div>
</div>


<!-- PROFILE UPDATE ERROR MODAL -->
<div id="errorModal" class="modal-overlay hidden">
    <div class="modal-box error-modal-box">

        <i class='bx bx-error-circle error-icon'></i>

        <h3>Update Failed</h3>

        <p>
            <?= htmlspecialchars($errorMessage) ?>
        </p>

        <button type="button" class="btn-error" onclick="closeErrorModal()">
            <i class='bx bx-x-circle'></i> OK
        </button>

    </div>
</div>

<script>
<?php if ($successMessage): ?>
    document.getElementById('successModal').classList.remove('hidden');
<?php endif; ?>

<?php if ($errorMessage): ?>
    document.getElementById('errorModal').classList.remove('hidden');
<?php endif; ?>
</script>

<script src="../assets/js/edit_profile.js"></script>

</body>
</html>