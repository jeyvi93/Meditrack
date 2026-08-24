    <?php

    session_start();
    require_once "../config/database.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    $medicineQuery = mysqli_query($conn, "
        SELECT id, medicine_name, batch_number, quantity
        FROM medicines
        WHERE status = 'Active'
        AND expiry_date >= CURDATE()
        AND is_quarantined = 0
        AND quantity > 0
        ORDER BY medicine_name ASC
    ");

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Consultation Log | MediTrack</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

        <!-- Icons -->
        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- CSS -->
        <link rel="stylesheet" href="../assets/css/consultation.css">
        <link rel="stylesheet" href="../assets/css/sidebar.css">
    </head>

    <body>
        <!-- SIDEBAR -->
        <?php include "../includes/sidebar.php"; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="bg-gradient"></div>
            <!-- HEADER -->
            <header class="content-header">
                <div class="title-area">
                    <h1>New Consultation Log</h1>
                    <p>
                        Record patient complaints, treatments, and medicines dispensed.
                    </p>
                </div>

                <a href="view_consultation.php" class="btn-secondary">
                    <i class="fas fa-list"></i>
                    View All Logs
                </a>
            </header>

            <!-- CONSULTATION FORM -->
            <form id="consultationForm">

                <!-- DATE & TIME -->
                <div class="input-grid two-cols">
                    <div class="input-group">
                        <label for="consultationDate">
                            Date
                        </label>
                        <input
                            type="date"
                            id="consultationDate"
                            name="consultation_date"
                            required
                        >
                    </div>

                    <div class="input-group">
                        <label for="consultationTime">
                            Time
                        </label>
                        <input
                            type="time"
                            id="consultationTime"
                            name="consultation_time"
                            required
                        >
                    </div>
                </div>

                <!-- SECTION 1: PATIENT INFORMATION -->
                <section class="form-card">
                    <div class="card-header">
                        <i class="fas fa-user-circle"></i>
                        <h2>Patient Information</h2>
                    </div>

                    <div class="input-grid two-cols">
                        <!-- PATIENT TYPE -->
                        <div class="input-group">
                            <label for="patientType">
                                Patient Type
                            </label>
                            <select
                                id="patientType"
                                name="patient_type"
                                onchange="togglePatientFields()"
                                required
                            >
                                <option value="student">
                                    Student
                                </option>
                                <option value="tstaff">
                                    Teaching Staff
                                </option>
                                <option value="nstaff">
                                    Non-Teaching Staff
                                </option>
                            </select>
                        </div>

                        <!-- FULL NAME -->
                        <div class="input-group">

                            <label for="fullName">
                                Full Name
                            </label>
                            <input
                                type="text"
                                id="fullName"
                                name="full_name"
                                placeholder="Enter Full Name"
                                required
                            >
                        </div>
                    </div>

                    <!-- AGE / SEX / CONTACT -->
                    <div class="input-grid three-cols">
                        <div class="input-group">
                            <label for="age">
                                Age
                            </label>
                            <input
                                type="number"
                                id="age"
                                name="age"
                                min="1"
                                placeholder="0"
                            >
                        </div>

                        <div class="input-group">
                            <label for="sex">
                                Sex
                            </label>
                            <select
                                id="sex"
                                name="sex"
                            >

                                <option value="Male">
                                    Male
                                </option>

                                <option value="Female">
                                    Female
                                </option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="contactNumber">
                                Contact Number
                            </label>

                            <input
                                type="tel"
                                id="contactNumber"
                                name="contact_number"
                                placeholder="09XX XXX XXXX"
                            >
                        </div>
                    </div>

                    <!-- DEPARTMENT -->
                    <div
                        class="input-grid"
                        id="sharedDepartmentField"
                    >
                        <div class="input-group">

                            <label for="department">
                                Department
                            </label>

                            <input
                                type="text"
                                id="department"
                                name="department"
                                placeholder="e.g. School of Technology"
                            >
                        </div>
                    </div>

                    <!-- STUDENT FIELDS -->
                    <div
                        class="input-grid two-cols conditional-fields"
                        id="studentFields"
                    >
                        <div class="input-group">
                            <label for="course">
                                Course
                            </label>

                            <input
                                type="text"
                                id="course"
                                name="course"
                                placeholder="e.g. BS Information Technology"
                            >
                        </div>

                        <div class="input-group">
                            <label for="yearLevel">
                                Year Level
                            </label>

                            <select
                                id="yearLevel"
                                name="year_level"
                            >

                                <option value="1st Year">
                                    1st Year
                                </option>

                                <option value="2nd Year">
                                    2nd Year
                                </option>

                                <option value="3rd Year">
                                    3rd Year
                                </option>

                                <option value="4th Year">
                                    4th Year
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- STAFF FIELDS -->
                    <div
                        class="input-grid conditional-fields"
                        id="staffFields"
                        style="display:none;"
                    >
                        <div class="input-group">
                            <label for="position">
                                Position
                            </label>

                            <input
                                type="text"
                                id="position"
                                name="position"
                                placeholder="e.g. Instructor I / Admin Assistant"
                            >
                        </div>
                    </div>
                </section>

                <!-- SECTION 2: CLINICAL ASSESSMENT -->
                <section class="form-card">
                    <div class="card-header">

                        <i class="fas fa-stethoscope"></i>

                        <h2>Clinical Assessment</h2>

                    </div>

                    <!-- COMPLAINT -->
                    <div class="input-group">
                        <label for="complaint">
                            Complaint
                        </label>

                        <select
                            id="complaint"
                            name="complaint"
                            onchange="toggleOtherComplaint()"
                            required
                        >
                            <option value="">
                                Select Complaint / Symptoms
                            </option>
                            <option value="Headache / Migraine">
                                Headache / Migraine
                            </option>
                            <option value="Fever">
                                Fever
                            </option>
                            <option value="Cough & Cold">
                                Cough & Cold
                            </option>

                            <option value="Stomach Ache">
                                Stomach Ache
                            </option>

                            <option value="Allergic Reaction">
                                Allergic Reaction
                            </option>

                            <option value="Dental Pain">
                                Dental Pain
                            </option>

                            <option value="Minor Injury">
                                Minor Injury
                            </option>

                            <option value="Dizziness">
                                Dizziness
                            </option>

                            <option value="Body Pain">
                                Body Pain
                            </option>

                            <option value="Nausea / Vomiting">
                                Nausea / Vomiting
                            </option>

                            <option value="Other">
                                Other
                            </option>
                        </select>
                    </div>

                    <div
                        class="input-group"
                        id="otherComplaintGroup"
                        style="display: none;"
                    >
                        <label for="otherComplaint">
                            Specify Complaint
                        </label>

                        <input
                            type="text"
                            id="otherComplaint"
                            placeholder="Enter complaint / symptoms"
                        >
                    </div>

                    <!-- TREATMENT / REMARKS -->
                    <div class="input-row two-cols">
                        <div class="input-group">
                            <label for="treatment">
                                Treatment Given
                            </label>

                            <textarea
                                id="treatment"
                                name="treatment"
                                rows="3"
                                placeholder="Procedures or first aid performed"
                            ></textarea>
                        </div>

                        <div class="input-group">
                            <label for="remarks">
                                Remarks
                            </label>
                            <textarea
                                id="remarks"
                                name="remarks"
                                rows="3"
                                placeholder="Observation or recommendations"
                            ></textarea>
                        </div>
                    </div>
                </section>

                <!-- SECTION 3: MEDICINES DISPENSED -->
                <section class="form-card">
                    <div class="card-header space-between">
                        <div class="header-title">

                            <i class="fas fa-pills"></i>

                            <h2>Medicines Dispensed</h2>

                        </div>

                        <button
                            type="button"
                            class="btn-add-row"
                            onclick="addMedicineRow()"
                        >
                            Add Medicine
                        </button>
                    </div>

                    <div id="medicineRows">
                        <!-- DEFAULT MEDICINE ROW -->
                        <div class="medicine-row">
                            <div class="input-group">
                                <label>
                                    Medicine Name
                                </label>

                                <select
                                    class="med-select"
                                    name="medicines[]"
                                >
                                    <option value="">
                                        Select Medicine from Inventory
                                    </option>

                                    <?php while ($medicine = mysqli_fetch_assoc($medicineQuery)): ?>

                                    <option value="<?= $medicine['id']; ?>">
                                        <?= htmlspecialchars($medicine['medicine_name']); ?>
                                        - <?= htmlspecialchars($medicine['batch_number']); ?>
                                    </option>

                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="input-group qty-input">
                                <label>
                                    Quantity
                                </label>

                                <input
                                    type="number"
                                    name="medicine_quantities[]"
                                    min="1"
                                    value="1"
                                >
                            </div>

                            <div class="row-action">
                                <!-- No delete button on first row -->
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FORM ACTIONS -->
                <div class="form-actions">
                    <button
                        type="reset"
                        class="btn-ghost"
                    >
                        Clear All
                    </button>

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        Save Consultation Log
                    </button>
                </div>
            </form>

        <!-- SUCCESS MODAL -->
<div class="success-modal-overlay" id="successModal">
    <div class="success-modal">

        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h2>Consultation Saved!</h2>

        <p>
            The consultation log has been successfully recorded.
        </p>

        <div class="success-actions">
            <button type="button" class="btn-secondary" onclick="closeSuccessModal()">
                Stay Here
            </button>

            <button type="button" class="btn-primary" onclick="viewConsultationLogs()">
                View Consultation Logs
            </button>
        </div>

    </div>
</div>
        </main>

    

        
    <script src="../assets/js/consultation.js"></script>
    <script src="../includes/includes.js"></script>

    </body>
    </html>