<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: students.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'Administrator') {
    header("Location: ../admin/accounts.php");
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
    <title>Add Student | MediTrack</title>

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
                        <h1>New Student Registration</h1>
                        <p>Create a complete student profile for the clinic system.</p>
                    </div>
                    <a href="students.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Students
                    </a>
                </header>

                <form id="studentForm" class="form-grid" action="../actions/add_student.php" method="POST">
                    
                    <!-- LEFT COLUMN -->
                    <div class="form-column">
                        <!-- ACADEMIC CARD -->
                        <section class="form-card">
                            <div class="card-header">
                                <i class="fas fa-graduation-cap"></i>
                                <h2>Academic Details</h2>
                            </div>
                            <div class="input-row">
                                <div class="input-group">
                                    <label>Student ID</label>
                                    <input type="text" name="student_id_number" placeholder="223-XXXXX" maxlength="9" pattern="[0-9]{3}-[0-9]{5}" title="Student ID must follow the format 223-XXXXX." required>
                                </div>
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Course</label>
                                    <input type="text" name="course" placeholder="BS Information Technology" pattern="[A-Za-z0-9 .&-]+" title="Course should contain valid letters, numbers, spaces, periods, or hyphens." required>
                                </div>
                                <div class="input-group">
                                    <label>Department</label>
                                    <select name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="Technology">Technology</option>
                                        <option value="Education">Education</option>
                                        <option value="Business">Business</option>
                                    </select>
                                </div>
                    
                                <div class="input-group">
                                    <label>Year Level</label>
                                    <select name="year_level" required>
                                        <option value="">Select Year</option>
                                        <option value="1">1st Year</option>
                                        <option value="2">2nd Year</option>
                                        <option value="3">3rd Year</option>
                                        <option value="4">4th Year</option>
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
                                <label for="last_name">Last Name</label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    placeholder="Enter last name"
                                    pattern="[A-Za-zÀ-ÿ\s.'-]+"
                                    title="Last name should contain letters and common name punctuation only."
                                    oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                                    required
                                >
                            </div>

                            <div class="input-group">
                                <label for="first_name">First Name</label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    placeholder="Enter first name"
                                    pattern="[A-Za-zÀ-ÿ\s.'-]+"
                                    title="First name should contain letters and common name punctuation only."
                                    oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                                    required
                                >
                            </div>

                            <div class="input-group">
                                <label for="middle_name">Middle Name <span class="middle-name-note">(Leave blank if none)</span></label>
                                <input
                                    type="text"
                                    id="middle_name"
                                    name="middle_name"
                                    placeholder="Enter middle name"
                                    pattern="[A-Za-zÀ-ÿ\s.'-]*"
                                    title="Middle name should contain letters and common name punctuation only."
                                    oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                                >
                            </div>

                            <div class="input-row three-cols">
                                <div class="input-group">
                                    <label>Birth Date</label>
                                    <input type="date" id="dobInput" name="date_of_birth" required>
                                </div>
                                <div class="input-group age-field">
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
                                    <input type="text" name="address" placeholder="Street, Barangay, City, Province" required>
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
                                <input type="text" name="emergency_name" placeholder="Guardian Full Name" pattern="[A-Za-zÀ-ÿ\s.,'-]+" title="Contact name should contain letters, spaces, and common name punctuation only." oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())" required>
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Relationship</label>
                                    <input type="text" name="emergency_relationship" placeholder="e.g. Mother" pattern="[A-Za-zÀ-ÿ\s-]+" title="Relationship should contain letters and spaces only." required>
                                </div>
                                <div class="input-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="emergency_contact" placeholder="09XXXXXXXXX" maxlength="11" minlength="11" pattern="09[0-9]{9}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Phone number must contain exactly 11 digits and start with 09." required    >
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
                                        <option value="">Select Blood Type</option>
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
                                    <input type="number" name="height" placeholder="170" min="1" step="1">
                                </div>

                                <div class="input-group">
                                    <label>Weight (kg)</label>
                                    <input type="number" name="weight" placeholder="60" min="1" step="1">
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
                         placeholder="e.g. Maxicare, Medicard">
                     </div>

             </div>
</section>

                        <div class="form-footer">
                            <button type="button" class="btn-ghost">Discard Draft</button>
                            <button type="submit" class="btn-primary">Save Student Record</button>
                        </div>

                        
                    </div>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="error-modal" id="errorModal">
                            <div class="error-modal-content">
                                <i class="fas fa-circle-exclamation"></i>

                                <h2>Unable to Add Student</h2>

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


                    

                </form>
            </div>
        </main>
    
<script src="../assets/js/add_patient.js"></script>
<script src="../includes/includes.js"></script>
</body>
</html>