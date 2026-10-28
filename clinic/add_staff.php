<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: staffs.php");
    exit();
}

$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff | MediTrack</title>

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/add_patient.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">

</head>
<body>

    <!-- SIDEBAR -->
    <?php include "../includes/sidebar.php"; ?>
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="bg-gradient"></div>
            
            <div class="content-wrapper">
                <!-- TOP ACTION BAR -->
                <header class="action-bar">
                    <div class="title-area">
                        <h1>New Staff Registration</h1>
                        <p>Create a complete staff profile for the clinic system.</p>
                    </div>
                    <a href="staffs.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Staffs
                    </a>
                </header>

                <form id="studentForm" class="form-grid" action="../actions/add_staff.php" method="POST">
                    
                    <!-- LEFT COLUMN -->
                    <div class="form-column">
                        <!-- ACADEMIC CARD -->
                        <section class="form-card">
                            <div class="card-header">
                                <i class="bx bx-briefcase"></i>
                                <h2>Employment Info</h2>
                            </div>
                            <div class="input-row">
                                <div class="input-group">
                                    <label>Staff ID</label>
                                    <input type="text"
                                        name="staff_id_number"
                                        placeholder="Enter Staff ID"
                                        maxlength="20"
                                        oninput="this.value = this.value.replace(/[A-Za-z]/g, '')"
                                        title="Staff ID can contain numbers and symbols only."
                                        required>
                                </div>

                                <div class="input-group">
                                    <label>Position</label>
                                    <select name="position" required>
                                        <option value="">Select Position</option>
                                        <option value="Teaching Staff">Teaching Staff</option>
                                        <option value="Non-Teaching Staff">Non-Teaching Staff</option>
                                    </select>
                                </div>

                                <div class="input-group">
                                    <label>Designation</label>
                                    <input type="text" name="designation" placeholder="Instructor" required>
                                </div>
                                <div class="input-group">
                                    <label>Department</label>
                                    <select name="department">
                                        <option value="">Select Department</option>
                                        <option value="Technology">Technology</option>
                                        <option value="Education">Education</option>
                                        <option value="Business">Business</option>
                                        <option value="Clinic">Clinic</option>
                                        <option value="Library">Library</option>
                                        <option value="Guidance Office">Guidance Office</option>
                                        <option value="Cashier">Cashier</option>
                                        <option value="Registrar">Registrar</option>
                                        <option value="Utility">Utility</option>
                                        <option value="Admin Office">Admin Office</option>
                                        <option value="SASO">SASO</option>
                                        <option value="CSU">CSU</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <!-- PERSONAL INFO CARD -->
                        <section class="form-card">
                            <div class="card-header">
                                <i class="fas fa-user"></i>
                                <h2>Personal Information</h2>
                            </div>
                            <div class="input-group">
                                <label for="first_name">First Name</label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    placeholder="First Name"
                                    pattern="[A-Za-zÀ-ÿ\s.'-]+"
                                    title="First name should contain letters and common name punctuation only."
                                    oninput="this.value = this.value.replace(/[0-9]/g, '')"
                                    required
                                >
                            </div>

                            <div class="input-group">
                                <label for="middle_name">Middle Name <span class="middle-name-note">(Leave blank if none)</span></label>
                                <input
                                    type="text"
                                    id="middle_name"
                                    name="middle_name"
                                    placeholder="Middle Name"
                                    pattern="[A-Za-zÀ-ÿ\s.'-]*"
                                    title="Middle name should contain letters and common name punctuation only."
                                    oninput="this.value = this.value.replace(/[0-9]/g, '')"
                                >
                            </div>

                            <div class="input-group">
                                <label for="last_name">Last Name</label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    placeholder="Last Name"
                                    pattern="[A-Za-zÀ-ÿ\s.'-]+"
                                    title="Last name should contain letters and common name punctuation only."
                                    oninput="this.value = this.value.replace(/[0-9]/g, '')"
                                    required
                                >
                            </div>
                            <div class="input-row three-cols">
                                <div class="input-group">
                                    <label>Birth Date</label>
                                    <input type="date" name="date_of_birth" id="dobInput" required>
                                </div>
                                <div class="input-group readonly">
                                    <label>Age</label>
                                    <input type="number" id="ageDisplay" readonly placeholder="--">
                                </div>
                                <div class="input-group">
                                    <label>Sex</label>
                                    <select name="sex" required>
                                        <option>Male</option>
                                        <option>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Contact Number</label>
                                    <input type="tel" name="contact_number" placeholder="09XXXXXXXXX" maxlength="11" minlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Contact number must contain exactly 11 digits and start with 09." required>
                                </div>
                                <div class="input-group">
                                    <label>Address</label>
                                    <input type="text" name="address" placeholder="Street, Barangay, City, Province">
                                </div>
                            </div>
                        </section>

                        <!-- EMERGENCY CONTACT -->
                        <section class="form-card">
                            <div class="card-header">
                                <i class="fas fa-phone-alt"></i>
                                <h2>Emergency Contact</h2>
                            </div>
                            <div class="input-group">
                                <label>Contact Name</label>
                                <input type="text" name="emergency_name" placeholder="Guardian Full Name" pattern="[A-Za-zÀ-ÿ\s.,'-]+" title="Contact name should contain letters, spaces, and common name punctuation only." required>
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Relationship</label>
                                    <input type="text" name="emergency_relationship" placeholder="e.g. Mother" pattern="[A-Za-zÀ-ÿ\s-]+" title="Relationship should contain letters and spaces only." required>
                                </div>
                                <div class="input-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="emergency_contact" placeholder="09XXXXXXXXX" maxlength="11" minlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Phone number must contain exactly 11 digits and start with 09." required>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="form-column">
                        <!-- MEDICAL RECORDS CARD -->
                        <section class="form-card medical-highlight">
                            <div class="card-header">
                                <i class="fas fa-file-medical-alt"></i>
                                <h2>Medical Record</h2>
                            </div>
                            <div class="input-row three-cols">
                                <div class="input-group">
                                    <label>Blood Type</label>
                                    <select name="blood_type">
                                        <option value="Unknown">Unknown</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Height (cm)</label>
                                    <input type="number" name="height" placeholder="170" min="1" step="0.01">
                                </div>
                                <div class="input-group">
                                    <label>Weight (kg)</label>
                                    <input type="number" name="weight" placeholder="60" min="1" step="0.01">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Allergies</label>
                                <textarea name="allergies" rows="2" placeholder="List food or drug allergies..."></textarea>
                            </div>
                            <div class="input-group">
                                <label>Medical History</label>
                                <textarea name="medical_history" rows="2" placeholder="Chronic illnesses, surgeries..."></textarea>
                            </div>
                            <div class="input-group">
                                <label>Current Medications</label>
                                <textarea name="current_medications" rows="2" placeholder="Maintenance medicine..."></textarea>
                            </div>
                            <div class="input-group">
                                <label>Vaccines</label>
                                <textarea name="vaccines" rows="2" placeholder="Immunization records..."></textarea>
                            </div>
                        </section>

                        <!-- INSURANCE INFORMATION CARD -->
                        <section class="form-card">

                            <div class="card-header">
                                <i class="fas fa-id-card"></i>
                                <h2>Insurance Information</h2>
                            </div>

                            <div class="input-group">
                                <label>PhilHealth Number</label>
                                <input 
                                    type="text" 
                                    name="philhealth_number" 
                                    placeholder="PhilHealth Number">
                            </div>

                        <div class="input-row two-cols">

                         <div class="input-group">
                            <label>Insurance Number</label>
                            <input 
                                type="text" 
                                name="insurance_number" 
                                placeholder="Insurance Policy Number">
                         </div>

                        <div class="input-group">
                            <label>Insurance Company</label>
                            <input 
                                type="text" 
                                name="insurance_company" 
                                placeholder="Insurance Provider">
                        </div>

                        </div>

                        </section>

                        <div class="form-footer">
                            <button type="button" class="btn-ghost">Discard Draft</button>
                            <button type="submit" class="btn-primary">Save Staff Record</button>
                        </div>
                    </div>

                </form>
            </div>
        </main>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-modal" id="errorModal">
                <div class="error-modal-content">
                    <i class="fas fa-circle-exclamation"></i>

                    <h2>Unable to Add Staff</h2>

                    <p><?php echo htmlspecialchars($errorMessage); ?></p>

                    <button type="button" onclick="closeErrorModal()">OK</button>
                </div>
            </div>

            <script>
                function closeErrorModal() {
                    document.getElementById("errorModal").style.display = "none";
                }
            </script>
        <?php endif; ?>


<script src="../assets/js/add_patient.js"></script>
<script src="../includes/includes.js"></script>
</body>
</html>