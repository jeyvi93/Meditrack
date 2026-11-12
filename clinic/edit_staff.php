<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: staffs.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'Administrator') {
    header("Location: ../admin/accounts.php");
    exit();
}

$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// GET STAFF ID
$staffId = (int)($_GET['id'] ?? 0);

if ($staffId <= 0) {
    header("Location: staffs.php");
    exit();
}

// FETCH STAFF RECORD
$stmt = $conn->prepare("SELECT * FROM staffs WHERE id = ? AND is_archived = 0 LIMIT 1");
$stmt->bind_param("i", $staffId);
$stmt->execute();

$staff = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$staff) {
    header("Location: staffs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff | MediTrack</title>

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/edit_patient.css">
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
                        <h1>Edit Staff Profile</h1>
                        <p>View staff information and update medical and emergency records.</p>
                    </div>
                    <a href="staffs.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to School Staff
                    </a>
                </header>

                <form id="studentForm" class="form-grid" action="../actions/update_staff.php" method="POST">

                    <input type="hidden" name="id" value="<?= (int)$staff['id'] ?>">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                    >
                    
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
                                        value="<?= htmlspecialchars($staff['staff_id_number'] ?? '') ?>"
                                        placeholder="Staff ID"
                                        maxlength="20"
                                        readonly>
                                </div>

                                <div class="input-group">
                                    <label>Position</label>
                                    <input
                                        type="text"
                                        name="position"
                                        value="<?= htmlspecialchars($staff['position'] ?? '') ?>"
                                        readonly
                                    >
                                </div>

                                <div class="input-group">
                                    <label>Designation</label>
                                    <input type="text"
                                        name="designation"
                                        value="<?= htmlspecialchars($staff['designation'] ?? '') ?>"
                                        placeholder="Instructor"
                                        readonly>
                                </div>
                                <div class="input-group">
                                    <label>Department</label>
                                    <input
                                        type="text"
                                        name="department"
                                        value="<?= htmlspecialchars($staff['department'] ?? '') ?>"
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
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    value="<?= htmlspecialchars($staff['last_name'] ?? '') ?>"
                                    readonly
                                >
                            </div>

                            <div class="input-group">
                                <label for="first_name">First Name</label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    value="<?= htmlspecialchars($staff['first_name'] ?? '') ?>"
                                    readonly
                                >
                            </div>

                            <div class="input-group">
                                <label for="middle_name">Middle Name <span class="middle-name-note">(Leave blank if none)</span></label>
                                <input
                                    type="text"
                                    id="middle_name"
                                    name="middle_name"
                                    value="<?= htmlspecialchars($staff['middle_name'] ?? '') ?>"
                                    readonly
                                >
                            </div>

                            <div class="input-row three-cols">
                                <div class="input-group">
                                    <label>Birth Date</label>
                                    <input
                                        type="date"
                                        name="date_of_birth"
                                        id="dobInput"
                                        value="<?= htmlspecialchars($staff['date_of_birth'] ?? '') ?>"
                                        readonly
                                    >
                                </div>
                                <div class="input-group readonly">
                                    <label>Age</label>
                                    <input
                                        type="text"
                                        id="ageDisplay"
                                        value="<?php
                                            if (!empty($staff['date_of_birth'])) {
                                                $dob = new DateTime($staff['date_of_birth']);
                                                echo $dob->diff(new DateTime())->y . ' years old';
                                            }
                                        ?>"
                                        readonly
                                    >
                                </div>
                                <div class="input-group">
                                    <label>Sex</label>
                                    <input
                                        type="text"
                                        name="sex"
                                        value="<?= htmlspecialchars($staff['sex'] ?? '') ?>"
                                        readonly
                                    >
                                </div>
                            </div>
                            <div class="input-row two-cols">
                                <div class="input-group">
                                    <label>Contact Number</label>
                                    <input
                                        type="tel"
                                        name="contact_number"
                                        value="<?= htmlspecialchars($staff['contact_number'] ?? '') ?>"
                                        readonly
                                    >
                                </div>
                                <div class="input-group">
                                    <label>Address</label>
                                    <input
                                        type="text"
                                        name="address"
                                        value="<?= htmlspecialchars($staff['address'] ?? '') ?>"
                                        readonly
                                    >
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
                                    value="<?= htmlspecialchars($staff['emergency_name'] ?? '') ?>"
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
                                        value="<?= htmlspecialchars($staff['emergency_relationship'] ?? '') ?>"
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
                                        value="<?= htmlspecialchars($staff['emergency_contact'] ?? '') ?>"
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
                                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bloodType): ?>
                                            <option value="<?= $bloodType ?>"
                                                <?= ($staff['blood_type'] ?? '') === $bloodType ? 'selected' : '' ?>>
                                                <?= $bloodType ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Height (cm)</label>
                                    <input
                                        type="number"
                                        name="height"
                                        value="<?= htmlspecialchars($staff['height'] ?? '') ?>"
                                        placeholder="170"
                                        min="1"
                                        step="0.01"
                                    >
                                </div>
                                <div class="input-group">
                                    <label>Weight (kg)</label>
                                    <input
                                        type="number"
                                        name="weight"
                                        value="<?= htmlspecialchars($staff['weight'] ?? '') ?>"
                                        placeholder="60"
                                        min="1"
                                        step="0.01"
                                    >
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Allergies</label>
                                <textarea name="allergies" rows="2" placeholder="List food or drug allergies..."><?= htmlspecialchars($staff['allergies'] ?? '') ?></textarea>
                            </div>
                            <div class="input-group">
                                <label>Medical History</label>
                                <textarea name="medical_history" rows="2" placeholder="Chronic illnesses, surgeries..."><?= htmlspecialchars($staff['medical_history'] ?? '') ?></textarea>
                            </div>
                            <div class="input-group">
                                <label>Current Medications</label>
                                <textarea name="current_medications" rows="2" placeholder="Maintenance medicine..."><?= htmlspecialchars($staff['current_medications'] ?? '') ?></textarea>
                            </div>
                            <div class="input-group">
                                <label>Vaccines</label>
                                <textarea name="vaccines" rows="2" placeholder="Immunization records..."><?= htmlspecialchars($staff['vaccines'] ?? '') ?></textarea>
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
                                    value="<?= htmlspecialchars($staff['philhealth_number'] ?? '') ?>"
                                    placeholder="PhilHealth Number"
                                >
                            </div>

                        <div class="input-row two-cols">

                         <div class="input-group">
                            <label>Insurance Number</label>
                            <input
                                type="text"
                                name="insurance_number"
                                value="<?= htmlspecialchars($staff['insurance_number'] ?? '') ?>"
                                placeholder="Insurance Policy Number"
                            >
                         </div>

                        <div class="input-group">
                            <label>Insurance Company</label>
                            <input
                                type="text"
                                name="insurance_company"
                                value="<?= htmlspecialchars($staff['insurance_company'] ?? '') ?>"
                                placeholder="Insurance Provider"
                            >
                        </div>

                        </div>

                        </section>

                        <div class="form-footer">
                            <button type="submit" class="btn-primary">Update Staff Record</button>
                        </div>
                    </div>

                </form>
            </div>
        </main>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-modal" id="errorModal">
                <div class="error-modal-content">
                    <i class="fas fa-circle-exclamation"></i>

                    <h2>Unable to Update Staff</h2>

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


<script src="../assets/js/edit_patient.js"></script>
<script src="../includes/includes.js"></script>
</body>
</html>