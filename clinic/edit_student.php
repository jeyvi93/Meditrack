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

require_once "../config/database.php";

$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$studentId || $studentId <= 0) {
    header("Location: students.php");
    exit();
}

$sql = "SELECT * FROM students WHERE id = ? AND is_archived = 0";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to load student record.");
}

mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    header("Location: students.php");
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
    <title>Edit Student | MediTrack</title>

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
                        <h1>Edit Student Record</h1>
                        <p>Update the student's medical and health information.</p>
                    </div>
                    <a href="students.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Students
                    </a>
                </header>

                <form id="studentForm" class="form-grid" action="../actions/update_student.php" method="POST">

                    <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
                    
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
                                    <input
                                        type="text"
                                        name="student_id_number"
                                        value="<?= htmlspecialchars($student['student_id_number'] ?? '') ?>"
                                        readonly
                                    >
                                </div>
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Course</label>
                                    <input
                                        type="text"
                                        name="course"
                                        value="<?= htmlspecialchars($student['course'] ?? '') ?>"
                                        readonly
                                    >
                                </div>
                                <div class="input-group">
                                    <label>Department</label>
                                    <input
                                        type="text"
                                        value="<?= htmlspecialchars($student['department'] ?? '') ?>"
                                        readonly
                                    >
                                </div>
                    
                                <div class="input-group">
                                    <label>Year Level</label>
                                    <input
                                        type="text"
                                        value="<?= match ((string)($student['year_level'] ?? '')) {
                                            '1' => '1st Year',
                                            '2' => '2nd Year',
                                            '3' => '3rd Year',
                                            '4' => '4th Year',
                                            default => ''
                                        } ?>"
                                        readonly
                                    >
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
                                <input type="text" id="last_name" name="last_name"
                                    value="<?= htmlspecialchars($student['last_name'] ?? '') ?>"
                                    readonly>
                            </div>

                            <div class="input-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name"
                                    value="<?= htmlspecialchars($student['first_name'] ?? '') ?>"
                                    readonly>
                            </div>

                            <div class="input-group">
                                <label for="middle_name">Middle Name <span class="middle-name-note">(Leave blank if none)</span></label>
                                <input type="text" id="middle_name" name="middle_name"
                                    value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>"
                                    readonly>
                            </div>

                            <div class="input-row three-cols">
                                <div class="input-group">
                                    <label>Birth Date</label>
                                    <input type="date" id="dobInput" name="date_of_birth"
                                        value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>"
                                        readonly>
                                </div>
                                <div class="input-group">
                                    <label>Age</label>
                                    <input
                                        type="text"
                                        id="ageDisplay"
                                        value="<?php
                                            if (!empty($student['date_of_birth'])) {
                                                $dob = new DateTime($student['date_of_birth']);
                                                echo $dob->diff(new DateTime())->y . ' years old';
                                            }
                                        ?>"
                                        readonly
                                    >
                                </div>
                                <div class="input-group">
                                    <label>Sex</label>
                                    <input type="text"
                                        value="<?= htmlspecialchars($student['sex'] ?? '') ?>"
                                        readonly>
                                </div>
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Contact Number</label>
                                    <input type="tel" name="contact_number"
                                        value="<?= htmlspecialchars($student['contact_number'] ?? '') ?>"
                                        readonly>
                                </div>
                                <div class="input-group">
                                    <label>Address</label>
                                    <input type="text" name="address"
                                        value="<?= htmlspecialchars($student['address'] ?? '') ?>"
                                        readonly>
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
                                <input
                                    type="text"
                                    name="emergency_name"
                                    value="<?= htmlspecialchars($student['emergency_name'] ?? '') ?>"
                                    placeholder="Guardian Full Name"
                                    pattern="[A-Za-zÀ-ÿ\s.,'-]+"
                                    title="Contact name should contain letters, spaces, and common name punctuation only."
                                    oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                                    required
                                >
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Relationship</label>
                                    <input
                                        type="text"
                                        name="emergency_relationship"
                                        value="<?= htmlspecialchars($student['emergency_relationship'] ?? '') ?>"
                                        placeholder="e.g. Mother"
                                        pattern="[A-Za-zÀ-ÿ\s-]+"
                                        title="Relationship should contain letters and spaces only."
                                        required
                                    >
                                </div>
                                <div class="input-group">
                                    <label>Phone Number</label>
                                    <input
                                        type="tel"
                                        name="emergency_contact"
                                        value="<?= htmlspecialchars($student['emergency_contact'] ?? '') ?>"
                                        placeholder="09XXXXXXXXX"
                                        maxlength="11"
                                        minlength="11"
                                        pattern="09[0-9]{9}"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        title="Phone number must contain exactly 11 digits and start with 09."
                                        required
                                    >
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
                                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $type): ?>
                                            <option value="<?= $type ?>" <?= ($student['blood_type'] ?? '') === $type ? 'selected' : '' ?>>
                                                <?= $type ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="input-group">
                                    <label>Height (cm)</label>
                                    <input
                                        type="number"
                                        name="height"
                                        value="<?= htmlspecialchars($student['height'] ?? '') ?>"
                                        placeholder="170"
                                        min="1"
                                        step="1"
                                    >
                                </div>

                                <div class="input-group">
                                    <label>Weight (kg)</label>
                                    <input
                                        type="number"
                                        name="weight"
                                        value="<?= htmlspecialchars($student['weight'] ?? '') ?>"
                                        placeholder="60"
                                        min="1"
                                        step="1"
                                    >
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Allergies</label>
                                <textarea name="allergies" rows="2" placeholder="List food or drug allergies..."><?= htmlspecialchars($student['allergies'] ?? '') ?></textarea>
                            </div>
                            <div class="input-group">
                                <label>Medical History</label>
                                <textarea name="medical_history" rows="2" placeholder="Chronic illnesses, surgeries..."><?= htmlspecialchars($student['medical_history'] ?? '') ?></textarea>
                            </div>
                            <div class="input-group">
                                <label>Current Medications</label>
                                <textarea name="current_medications" rows="2" placeholder="Maintenance medicine..."><?= htmlspecialchars($student['current_medications'] ?? '') ?></textarea>
                            </div>
                            <div class="input-group">
                                <label>Vaccines</label>
                                <textarea name="vaccines" rows="2" placeholder="Immunization records..."><?= htmlspecialchars($student['vaccines'] ?? '') ?></textarea>
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
                            value="<?= htmlspecialchars($student['philhealth_number'] ?? '') ?>"
                            placeholder="PhilHealth Number"
                        >
                    </div>

                    <div class="input-row two-cols">

                         <div class="input-group">
                            <label>Insurance Number</label>
                            <input
                                type="text"
                                name="insurance_number"
                                value="<?= htmlspecialchars($student['insurance_number'] ?? '') ?>"
                                placeholder="Insurance Policy Number"
                            >
                    </div>

                    <div class="input-group">
                     <label>Insurance Company</label>
                        <input
                            type="text"
                            name="insurance_company"
                            value="<?= htmlspecialchars($student['insurance_company'] ?? '') ?>"
                            placeholder="e.g. Maxicare, Medicard"
                        >
                     </div>

             </div>
</section>

                        <div class="form-footer">
                            <button type="submit" class="btn-primary">Update Student Record</button>
                        </div>

                        
                    </div>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="error-modal" id="errorModal">
                            <div class="error-modal-content">
                                <i class="fas fa-circle-exclamation"></i>

                                <h2>Unable to Update Student</h2>

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