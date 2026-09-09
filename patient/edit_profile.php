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
                <div class="avatar-circle">JD</div>
                <div class="header-text">
                    <h2>Update Information</h2>
                    <p>Personalize your clinic profile details.</p>
                </div>
            </div>

            <form action="#" class="edit-form" onsubmit="handleProfileSubmit(event)">
                <div class="info-grid">
                    <div class="info-group">
                        <label>Full Name</label>
                        <div class="input-box">
                            <i class='bx bx-user'></i>
                            <input type="text" value="Johnathan Alexander Doe" required>
                        </div>
                    </div>

                    <div class="info-group">
                        <label>Email Address</label>
                        <div class="input-box">
                            <i class='bx bx-envelope'></i>
                            <input type="email" value="j.doe@university.edu.ph" required>
                        </div>
                    </div>

                    <div class="info-group">
                        <label>ID Number</label>
                        <div class="input-box disabled">
                            <i class='bx bx-id-card'></i>
                            <input type="text" value="2023-00451-MN-0" disabled>
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
            Your profile information has been successfully updated.
        </p>

        <button type="button" class="btn-success" onclick="closeSuccessModal()">
            <i class='bx bx-check'></i> Done
        </button>

    </div>
</div>
<script src="../assets/js/edit_profile.js"></script>

</body>
</html>