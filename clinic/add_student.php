<?php
session_start();

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
                                    <input type="text" name="student_id_number" placeholder="223-XXXXX" required>
                                </div>
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Course</label>
                                    <input type="text" name="course" placeholder="BS IT" required>
                                </div>
                                <div class="input-group">
                                    <label>Department</label>
                                    <input type="text" name="department" placeholder="Technology">
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
                                <label>Full Name</label>
                                <input type="text" name="full_name" placeholder="Last Name, First Name Middle Name" required>
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
                                    <input type="tel" name="contact_number" placeholder="09XX XXX XXXX" required>
                                </div>
                                <div class="input-group">
                                    <label>Address</label>
                                    <input type="text" name="address" placeholder="City / Province">
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
                                <input type="text" name="emergency_name" placeholder="Guardian Full Name">
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Relationship</label>
                                    <input type="text" name="emergency_relationship"placeholder="e.g. Mother">
                                </div>
                                <div class="input-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="emergency_contact" placeholder="09XX XXX XXXX">
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
                                        <option>A+</option><option>A-</option>
                                        <option>B+</option><option>B-</option>
                                        <option>O+</option><option>O-</option>
                                        <option>AB+</option><option>AB-</option>
                                        <option>Unknown</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Height (cm)</label>
                                    <input type="number" name="height" placeholder="170">
                                </div>
                                <div class="input-group">
                                    <label>Weight (kg)</label>
                                    <input type="number" name="weight" placeholder="60">
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

                    


                    

                </form>
            </div>
        </main>
    
<script src="../assets/js/add_patient.js"></script>
<script src="../includes/includes.js"></script>
</body>
</html>