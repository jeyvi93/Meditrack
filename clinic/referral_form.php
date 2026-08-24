<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Form | MediTrack</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
     <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/referral_form.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>

    <!-- SIDEBAR -->
        <?php include "../includes/sidebar.php"; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="bg-gradient"></div>

            <header class="content-header">
                <div class="title-area">
                    <h1>Patient Referral Form</h1>
                    <p>Issue a formal referral to external medical facilities or specialists.</p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.location.href='referral_history.php'"><i class="fas fa-list"></i> Referral History</button>
                </div>
            </header>

            <form id="referralForm" method="POST" action="../actions/referral_save.php">
                
                <!-- SECTION 1: REFERRAL INFORMATION -->
                <section class="form-card highlight-blue">
                    <div class="card-header">
                        <i class="fas fa-paper-plane"></i>
                        <h2>Referral Information</h2>
                    </div>
                    <div class="input-grid three-cols">
                        <div class="input-group">
                            <label>Referred Date</label>
                            <input type="date" id="refDate" name="referred_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="input-group">
                            <label>Doctor's Name</label>
                            <input type="text" id="DoctorName" name="doctor_name" placeholder="e.g. Dr. Smith" required>
                        </div>
                        <div class="input-group">
                            <label for="reason">Reason for Referral</label>
                            <textarea id="reason" name="reason_for_referral" rows="2" placeholder="Enter the reason for referring the patient..." required></textarea>
                        </div>
                    </div>
                </section>

                <!-- SECTION 2: PATIENT INFORMATION -->
                <section class="form-card">
                    <div class="card-header">
                        <i class="fas fa-user-injured"></i>
                        <h2>Patient Information</h2>
                    </div>
                    <div class="input-grid three-cols">
                        <div class="input-group">
                            <label>Full Name</label>
                            <input type="text" id="fullName" name="full_name" placeholder="Last Name, First Name Middle" required>
                        </div>
                        <div class="input-group">
                            <label>Date of Birth</label>
                            <input type="date" id="dobInput" name="date_of_birth" required>
                        </div>
                        <div class="input-group readonly">
                            <label>Age</label>
                            <input type="number" id="ageDisplay" readonly placeholder="--">
                        </div>
                    </div>
                    <div class="input-grid two-cols">
                        <div class="input-group">
                            <label>Sex</label>
                            <select id="sex" name="sex" required>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Contact Number</label>
                            <input type="tel" id="contact" name="contact_number" placeholder="09XX XXX XXXX">
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Home Address</label>
                        <input type="text" id="address" name="address" placeholder="Complete Street, City, Province">
                    </div>
                </section>

                <!-- SECTION 3: EMERGENCY & INSURANCE -->
                <div class="input-grid two-cols">
                    <section class="form-card">
                        <div class="card-header">
                            <i class="fas fa-phone-alt"></i>
                            <h2>Emergency Contact</h2>
                        </div>
                        <div class="input-grid">
                            <div class="input-group">
                                <label>Parent Name (Father or Mother)</label>
                                <input type="text" id="parent_name" name="parent_name" placeholder="Full Name">
                            </div>
                            <div class="input-group">
                                <label>Parent's Date of Birth</label>
                                <input type="date" id="parent_dob" name="parent_dob">
                            </div>
                            <div class="input-group">
                                <label>Spouse Name (if any)</label>
                                <input type="text" id="spouse_name" name="spouse_name" placeholder="Full Name (Optional)">
                            </div>
                            <div class="input-group">
                                <label>Spouse's Date of Birth</label>
                                <input type="date" id="spouse_dob" name="spouse_dob">
                            </div>
                        </div>
                    </section>

                    <section class="form-card">
                        <div class="card-header">
                            <i class="fas fa-shield-alt"></i>
                            <h2>Insurance</h2>
                        </div>
                        <div class="input-group">
                            <label>PhilHealth Member?</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="philhealth" value="yes"> Yes
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="philhealth" value="no" checked> No
                                </label>
                            </div>
                        </div>
                        <!-- CONDITIONAL FIELD -->
                        <div class="input-group conditional-input" id="philhealthSpec">
                            <label>Specify PhilHealth Number / Details</label>
                            <input type="text" id="philhealthInput" name="philhealth_number" placeholder="Enter PhilHealth ID or Category">
                        </div>
                    </section>
                </div>

                <!-- SECTION 4: CLINICAL NOTES -->
                <section class="form-card highlight-teal">
                    <div class="card-header">
                        <i class="fas fa-file-medical"></i>
                        <h2>Clinical Notes</h2>
                    </div>
                    <div class="input-row two-cols">
                        <div class="input-group">
                            <label>Nurse's Notes</label>
                            <textarea id="nurseNotes" name="nurse_notes" rows="4" placeholder="Enter observations, referral notes, and instructions for the receiving physician..."></textarea>
                        </div>
                        <div class="input-group">
                            <label>Weight (kg)</label>
                            <input type="number" id="weight" name="weight" step="0.1" placeholder="0.0">
                        </div>
                    </div>
                    <div class="input-row two-cols">
                        <div class="input-group">
                            <label>Blood Pressure</label>
                            <input type="text" id="bp" name="blood_pressure" placeholder="e.g. 120/80">
                        </div>
                        <div class="input-group">
                            <label>Temperature (°C)</label>
                            <input type="number" id="temp" name="temperature" step="0.1" placeholder="36.5">
                        </div>
                    </div>
                </section>

                <!-- ACTION BUTTONS -->
                <div class="form-actions">
                    <button type="reset" class="btn-ghost">Discard Draft</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-print"></i> Save & Print Referral
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script src="../assets/js/referral_form.js"></script>
    <script src="../includes/includes.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</body>
</html>