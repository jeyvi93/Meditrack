<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

$sql = "SELECT * FROM staffs ORDER BY full_name ASC";
$result = mysqli_query($conn, $sql);

// ==========================
// PAGINATION
// ==========================

$recordsPerPage = 15;

$currentPage = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

// TOTAL RECORDS
$countQuery = "SELECT COUNT(*) AS total FROM staffs";

$countResult = mysqli_query($conn, $countQuery);

$totalRecords = (int) mysqli_fetch_assoc($countResult)['total'];

// TOTAL PAGES
$totalPages = ceil($totalRecords / $recordsPerPage);

// OFFSET
$offset = ($currentPage - 1) * $recordsPerPage;

// GET CURRENT PAGE RECORDS
$sql = "
    SELECT *
    FROM staffs
    ORDER BY full_name ASC
    LIMIT $recordsPerPage OFFSET $offset
";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Staff Profiles | MediTrack</title>

    <!-- GLOBAL CSS -->
    <link rel="stylesheet" href="../assets/css/sidebar.css">

    <!-- MODULE CSS -->
    <link rel="stylesheet" href="../assets/css/staffs.css">

    <!-- ICONS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<!-- SIDEBAR INCLUDE -->
<?php include "../includes/sidebar.php"; ?>

<!-- MAIN CONTENT -->
<main class="main-content">

    <!-- TOP HEADER -->
    <header class="module-header">
        <div class="title-area">
            <h1>School Staff Profiles</h1>
            <p>Manage and monitor staff information and records.</p>
        </div>

        <button class="btn-primary" onclick="window.location.href='add_staff.php'" >
            <i class='bx bx-user-plus'></i> Add New Staff
        </button>
    </header>

    <!-- FILTER + SEARCH -->
    <section class="filter-section">

        <div class="search-box">
            <i class='bx bx-search'></i>
            <input type="text" id="staffSearch" placeholder="Search by ID or Name...">
        </div>

        <div class="filters">

    <div class="filter-group">
        <label for="department">Department</label>
        <select id="department" class="filter-select">
            <option value="all">All Departments</option>
            <option value="technology">Technology</option>
            <option value="education">Education</option>
            <option value="business">Business</option>
            <option value="clinic">Clinic</option>
            <option value="library">Library</option>
            <option value="guidance">Guidance Office</option>
            <option value="cashier">Cashier</option>
            <option value="registrar">Registrar</option>
            <option value="utility">Utility</option>
            <option value="admin">Admin Office</option>
            <option value="saso">SASO</option>
            <option value="csu">CSU</option>
        </select>
    </div>

    <div class="filter-group">
        <label for="position">Position</label>
        <select id="position" class="filter-select">
            <option value="all">All Positions</option>
            <option value="teaching staff">Teaching Staff</option>
            <option value="non-teaching staff">Non-Teaching Staff</option>
        </select>
    </div>

</div>

    </section>

    <!-- TABLE -->
    <div class="table-container">
        <div class="table-wrapper">

            <table>
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Full Name</th>
                        <th>Sex</th>
                        <th>Position</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($staff = mysqli_fetch_assoc($result)) : ?>

<tr>

    <td>
        <strong><?= htmlspecialchars($staff['staff_id_number']); ?></strong>
    </td>

    <td>
        <?= htmlspecialchars($staff['full_name']); ?>
    </td>

    <td>
        <?= htmlspecialchars($staff['sex']); ?>
    </td>

    <td>
        <?= htmlspecialchars($staff['position']); ?>
    </td>

    <td>
        <?= htmlspecialchars($staff['designation']); ?>
    </td>

    <td>
        <?= htmlspecialchars($staff['department']); ?>
    </td>

    <td>

        <div class="action-buttons">

            <!-- View Button -->
            <button
                class="icon-btn view"
                title="View"
                onclick='openStaffModal({

                    id: <?= json_encode($staff["staff_id_number"]) ?>,
                    name: <?= json_encode($staff["full_name"]) ?>,
                    dob: <?= json_encode($staff["date_of_birth"]) ?>,
                    age: <?= json_encode(date_diff(date_create($staff["date_of_birth"]), date_create("today"))->y) ?>,

                    sex: <?= json_encode($staff["sex"]) ?>,
                    position: <?= json_encode($staff["position"]) ?>,
                    designation: <?= json_encode($staff["designation"]) ?>,
                    department: <?= json_encode($staff["department"]) ?>,

                    contact: <?= json_encode($staff["contact_number"]) ?>,
                    address: <?= json_encode($staff["address"]) ?>,

                    blood: <?= json_encode($staff["blood_type"]) ?>,
                    height: <?= json_encode($staff["height"]) ?>,
                    weight: <?= json_encode($staff["weight"]) ?>,
                    allergies: <?= json_encode($staff["allergies"]) ?>,
                    history: <?= json_encode($staff["medical_history"]) ?>,
                    medications: <?= json_encode($staff["current_medications"]) ?>,
                    vaccines: <?= json_encode($staff["vaccines"]) ?>,

                    philhealth: <?= json_encode($staff["philhealth_number"]) ?>,
                    insuranceNumber: <?= json_encode($staff["insurance_number"]) ?>,
                    insuranceCompany: <?= json_encode($staff["insurance_company"]) ?>,

                    emergencyName: <?= json_encode($staff["emergency_name"]) ?>,
                    relationship: <?= json_encode($staff["emergency_relationship"]) ?>,
                    emergencyNumber: <?= json_encode($staff["emergency_contact"]) ?>

                })'>

                <i class="bx bx-show"></i>

            </button>

            <!-- Edit Button -->
            <button
                class="icon-btn edit"
                title="Edit"
                onclick='openEditStaffModal({

                    id: <?= json_encode($staff["staff_id_number"]) ?>,
                    name: <?= json_encode($staff["full_name"]) ?>,
                    dob: <?= json_encode($staff["date_of_birth"]) ?>,
                    age: <?= json_encode(date_diff(date_create($staff["date_of_birth"]), date_create("today"))->y) ?>,

                    sex: <?= json_encode($staff["sex"]) ?>,
                    position: <?= json_encode($staff["position"]) ?>,
                    designation: <?= json_encode($staff["designation"]) ?>,
                    department: <?= json_encode($staff["department"]) ?>,

                    contact: <?= json_encode($staff["contact_number"]) ?>,
                    address: <?= json_encode($staff["address"]) ?>,

                    blood: <?= json_encode($staff["blood_type"]) ?>,
                    height: <?= json_encode($staff["height"]) ?>,
                    weight: <?= json_encode($staff["weight"]) ?>,
                    allergies: <?= json_encode($staff["allergies"]) ?>,
                    history: <?= json_encode($staff["medical_history"]) ?>,
                    medications: <?= json_encode($staff["current_medications"]) ?>,
                    vaccines: <?= json_encode($staff["vaccines"]) ?>,

                    philhealth: <?= json_encode($staff["philhealth_number"]) ?>,
                    insuranceNumber: <?= json_encode($staff["insurance_number"]) ?>,
                    insuranceCompany: <?= json_encode($staff["insurance_company"]) ?>,

                    emergencyName: <?= json_encode($staff["emergency_name"]) ?>,
                    relationship: <?= json_encode($staff["emergency_relationship"]) ?>,
                    emergencyNumber: <?= json_encode($staff["emergency_contact"]) ?>

                })'>

                <i class="bx bx-edit"></i>

            </button>

            <!-- Delete Button -->
            <button
                class="icon-btn delete"
                title="Delete"
                onclick='openDeleteStaffModal({

                    id: <?= json_encode($staff["staff_id_number"]) ?>,
                    name: <?= json_encode($staff["full_name"]) ?>

                })'>

                <i class="bx bx-trash"></i>

            </button>

        </div>

    </td>

</tr>

<?php endwhile; ?>

                </tbody>
            </table>
                <!-- TABLE FOOTER -->
                <div class="table-footer">

                    <p>
                        Showing
                        <?= $totalRecords > 0 ? (($currentPage - 1) * $recordsPerPage + 1) : 0 ?>
                        -
                        <?= min($currentPage * $recordsPerPage, $totalRecords) ?>
                        of
                        <?= $totalRecords ?>
                        <?= $totalRecords === 1 ? 'record' : 'records' ?>
                    </p>

                    <?php if ($totalPages > 1): ?>

                    <div class="pagination">

                        <!-- PREVIOUS -->
                        <?php if ($currentPage > 1): ?>

                        <a
                            href="?page=<?= $currentPage - 1 ?>"
                            class="btn-page"
                            >
                            <i class="bx bx-chevron-left"></i>
                        </a>

                        <?php else: ?>

                        <button
                            class="btn-page"
                            disabled
                            >
                            <i class="bx bx-chevron-left"></i>
                        </button>

                        <?php endif; ?>


                        <!-- PAGE NUMBERS -->
                        <?php for ($page = 1; $page <= $totalPages; $page++): ?>

                        <?php if ($page == $currentPage): ?>

                            <a
                                href="?page=<?= $page ?>"
                                class="btn-page active"
                                >
                                <?= $page ?>
                            </a>

                        <?php else: ?>

                            <a
                                href="?page=<?= $page ?>"
                                class="btn-page"
                                >
                                <?= $page ?>
                            </a>

                        <?php endif; ?>

                        <?php endfor; ?>


                        <!-- NEXT -->
                        <?php if ($currentPage < $totalPages): ?>

                            <a
                                href="?page=<?= $currentPage + 1 ?>"
                                class="btn-page"
                                >
                                <i class="bx bx-chevron-right"></i>
                            </a>

                        <?php else: ?>

                            <button
                                class="btn-page"
                                disabled
                                >
                                <i class="bx bx-chevron-right"></i>
                            </button>

                        <?php endif; ?>

                    </div>

                    <?php endif; ?>

                </div>
        </div>
    </div>

</main>

<!-- VIEW STAFF MODAL -->
<div id="staffModal" class="modal">
    <div class="modal-content modal-container view-mode">

        <span class="close" onclick="closeStaffModal()">&times;</span>

        <!-- HEADER SUMMARY -->
        <div class="staff-summary">

            <div class="profile-avatar p-avatar">
                <i class='bx bx-user'></i>
            </div>

            <div class="summary-info">
                <h1 id="m_fullName">Full Name</h1>
                <p id="m_staffId">Staff ID</p>
            </div>

        </div>
        

        <!-- PERSONAL INFO -->
        <div class="info-section">
            <div class="info-title">Personal Information</div>

            <div class="info-grid">

                <div class="form-group">
                    <label>Date of Birth</label>
                    <p id="m_dob">--</p>
                </div>

                <div class="form-group">
                    <label>Age</label>
                    <p id="m_age">--</p>
                </div>

                <div class="form-group">
                    <label>Sex</label>
                    <p id="m_sex">--</p>
                </div>

                <div class="form-group">
                    <label>Position</label>
                    <p id="m_position">--</p>
                </div>

                <div class="form-group">
                    <label>Designation</label>
                    <p id="m_designation">--</p>
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <p id="m_department">--</p>
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <p id="m_contact">--</p>
                </div>

                <div class="form-group full-width">
                    <label>Address</label>
                    <p id="m_address">--</p>
                </div>

            </div>
        </div>

        <!-- HEALTH INFO -->
        <div class="info-section">
            <div class="info-title">Health Information</div>

            <div class="info-grid">

                <div class="form-group">
                    <label>Blood Type</label>
                    <p id="m_blood">--</p>
                </div>

                <div class="form-group">
                    <label>Height</label>
                    <p id="m_height">--</p>
                </div>

                <div class="form-group">
                    <label>Weight</label>
                    <p id="m_weight">--</p>
                </div>

                <div class="form-group">
                    <label>Allergies</label>
                    <p id="m_allergies">--</p>
                </div>

                <div class="form-group">
                    <label>Medical History</label>
                    <p id="m_history">--</p>
                </div>

                <div class="form-group">
                    <label>Medications</label>
                    <p id="m_medications">--</p>
                </div>

                <div class="form-group full-width">
                    <label>Vaccines</label>
                    <p id="m_vaccines">--</p>
                </div>

            </div>
        </div>

        <!-- INSURANCE INFORMATION -->
<div class="info-section">

    <div class="info-title">Insurance Information</div>

    <div class="info-grid">

        <div class="form-group">
            <label>PhilHealth Number</label>
            <p id="m_philhealth">--</p>
        </div>

        <div class="form-group">
            <label>Insurance Number</label>
            <p id="m_insuranceNumber">--</p>
        </div>

        <div class="form-group full-width">
            <label>Insurance Company</label>
            <p id="m_insuranceCompany">--</p>
        </div>

    </div>

</div>

        <!-- EMERGENCY INFO -->
        <div class="info-section">
            <div class="info-title">Emergency Contact</div>

            <div class="info-grid">

                <div class="form-group">
                    <label>Name</label>
                    <p id="m_emergencyName">--</p>
                </div>

                <div class="form-group">
                    <label>Relationship</label>
                    <p id="m_relationship">--</p>
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <p id="m_emergencyNumber">--</p>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- EDIT STAFF MODAL -->
<div id="editStaffModal" class="modal">
    <form
     action="../actions/update_staff.php"
     method="POST"
      class="modal-content modal-container edit-mode">

        <span class="close" onclick="closeEditStaffModal()">&times;</span>

        <!-- HEADER SUMMARY -->
        <div class="staff-summary">

            <div class="profile-avatar p-avatar">
                <i class='bx bx-user'></i>
            </div>

            <div class="summary-info">
                <input type="text" id="e_fullName" name="full_name" placeholder="Full Name">
                <input type="hidden" id="e_staffId" name="staff_id_number">
                <input type="text" id="e_staffIdDisplay" readonly>
            </div>

        </div>

        <!-- PERSONAL INFO -->
        <div class="info-section">
            <div class="info-title">Personal Information</div>

            <div class="info-grid">

                <div class="data-group">
                    <label>Date of Birth</label>
                    <input type="date" id="e_dob" name="date_of_birth">
                </div>

                <div class="data-group">
                    <label>Age</label>
                    <input type="number" id="e_age" readonly>
                </div>

                <div class="data-group">
                    <label>Sex</label>
                    <select id="e_sex" name="sex">
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>

                <div class="data-group">
                    <label>Position</label>
                    <input type="text" id="e_position" name="position">
                </div>

                <div class="data-group">
                    <label>Designation</label>
                    <input type="text" id="e_designation" name="designation">
                </div>

                <div class="data-group">
                    <label>Department</label>
                    <input type="text" id="e_department" name="department">
                </div>

                <div class="data-group">
                    <label>Contact Number</label>
                    <input type="text" id="e_contact" name="contact_number">
                </div>

                <div class="data-group full-width">
                    <label>Address</label>
                    <input type="text" id="e_address" name="address">
                </div>

            </div>
        </div>

        <!-- HEALTH INFO -->
        <div class="info-section">
            <div class="info-title">Health Information</div>

            <div class="info-grid">

                <div class="data-group">
                    <label>Blood Type</label>
                    <input type="text" id="e_blood" name="blood_type">
                </div>

                <div class="data-group">
                    <label>Height</label>
                    <input type="text" id="e_height" name="height">
                </div>

                <div class="data-group">
                    <label>Weight</label>
                    <input type="text" id="e_weight" name="weight">
                </div>

                <div class="data-group">
                    <label>Allergies</label>
                    <input type="text" id="e_allergies" name="allergies">
                </div>

                <div class="data-group">
                    <label>Medical History</label>
                    <input type="text" id="e_history" name="medical_history" readonly>
                </div>

                <div class="data-group">
                    <label>Medications</label>
                    <input type="text" id="e_medications" name="current_medications">
                </div>

                <div class="data-group full-width">
                    <label>Vaccines</label>
                    <input type="text" id="e_vaccines" name="vaccines">
                </div>

            </div>
        </div>

        <!-- INSURANCE INFORMATION -->
<div class="info-section">

    <div class="info-title">Insurance Information</div>

    <div class="info-grid">

        <div class="data-group">
            <label>PhilHealth Number</label>
            <input
                type="text"
                id="e_philhealth"
                name="philhealth_number">
        </div>

        <div class="data-group">
            <label>Insurance Number</label>
            <input
                type="text"
                id="e_insuranceNumber"
                name="insurance_number">
        </div>

        <div class="data-group full-width">
            <label>Insurance Company</label>
            <input
                type="text"
                id="e_insuranceCompany"
                name="insurance_company">
        </div>

    </div>

</div>

        <!-- EMERGENCY INFO -->
        <div class="info-section">
            <div class="info-title">Emergency Contact</div>

            <div class="info-grid">

                <div class="data-group">
                    <label>Name</label>
                    <input type="text" id="e_emergencyName" name="emergency_name">
                </div>

                <div class="data-group">
                    <label>Relationship</label>
                    <input type="text" id="e_relationship" name="emergency_relationship">
                </div>

                <div class="data-group">
                    <label>Contact Number</label>
                    <input type="text" id="e_emergencyNumber" name="emergency_contact">
                </div>

            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeEditStaffModal()">Cancel</button>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>

    </form>
</div>
<!-- DELETE STAFF MODAL -->
<div id="deleteStaffModal" class="modal">
    <div class="modal-content modal-container delete-mode">

        <span class="close" onclick="closeDeleteStaffModal()">&times;</span>

        <!-- ICON / WARNING -->
        <div class="delete-icon">
            <i class='bx bx-error-circle'></i>
        </div>

        <div class="delete-text">
            <h2>Delete Staff?</h2>
            <p>
                Are you sure you want to delete
                <strong id="d_staffName">this staff member</strong>?
                This action cannot be undone.
            </p>
        </div>

        <!-- HIDDEN ID HOLDER -->
        <input type="hidden" id="d_staffId">

        <!-- ACTION BUTTONS -->
        <div class="modal-actions">
            <button class="btn-secondary" onclick="closeDeleteStaffModal()">
                Cancel
            </button>

            <button class="btn-danger" onclick="confirmDeleteStaff()">
                Yes, Delete
            </button>
        </div>

    </div>
</div>

<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>

<div id="messageModal" class="modal active">

    <div class="modal-content message-modal">

        <div class="message-icon success">
            <i class='bx bx-check-circle'></i>
        </div>

        <h2>
            <?= isset($_SESSION['success']) ? "Success" : "Error"; ?>
        </h2>

        <p>
            <?= isset($_SESSION['success'])
                ? $_SESSION['success']
                : $_SESSION['error']; ?>
        </p>

        <div class="modal-actions">
            <button class="btn-primary" onclick="closeMessageModal()">
                OK
            </button>
        </div>

    </div>

</div>

<?php
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<?php endif; ?>

<!-- SCRIPTS -->
<script src="../includes/includes.js"></script>
<script src="../assets/js/staffs.js"></script>

</body>
</html>