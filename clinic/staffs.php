<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

if (($_SESSION['role'] ?? '') === 'Administrator') {
    header("Location: ../admin/accounts.php");
    exit();
}

$role = $_SESSION['role'] ?? '';

// ==========================
// SEARCH + FILTER + PAGINATION
// ==========================

$recordsPerPage = 20;

$currentPage = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

// ==========================
// SEARCH
// ==========================

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

// ==========================
// DEPARTMENT FILTER
// ==========================

$department = isset($_GET['department'])
    ? trim($_GET['department'])
    : '';

// ==========================
// POSITION FILTER
// ==========================

$position = isset($_GET['position'])
    ? trim($_GET['position'])
    : '';

// ==========================
// BUILD WHERE CONDITION
// ==========================

$where = ["is_archived = 0"];
$params = [];
$types = "";

if ($search !== '') {

    $where[] = "(
        staff_id_number LIKE ?
        OR last_name LIKE ?
        OR first_name LIKE ?
        OR middle_name LIKE ?
        OR sex LIKE ?
        OR position LIKE ?
        OR designation LIKE ?
        OR department LIKE ?
    )";

    $searchParam = "%" . $search . "%";

    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;

    $types .= "ssssssss";
}

if ($department !== '' && $department !== 'all') {

    $where[] = "LOWER(department) = ?";
    $params[] = strtolower($department);

    $types .= "s";
}

if ($position !== '' && $position !== 'all') {

    $where[] = "LOWER(position) = ?";
    $params[] = strtolower($position);

    $types .= "s";
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

// ==========================
// COUNT FILTERED RECORDS
// ==========================

$countSql = "SELECT COUNT(*) AS total FROM staffs $whereSql";

$countStmt = mysqli_prepare($conn, $countSql);

if (!$countStmt) {
    die("Count prepare failed: " . mysqli_error($conn));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}

mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);

$totalRecords = (int) mysqli_fetch_assoc($countResult)['total'];

mysqli_stmt_close($countStmt);

// ==========================
// TOTAL PAGES
// ==========================

$totalPages = ceil($totalRecords / $recordsPerPage);

// ==========================
// PREVENT INVALID PAGE
// ==========================

if ($totalPages > 0 && $currentPage > $totalPages) {
    $currentPage = $totalPages;
}

if ($totalPages === 0) {
    $currentPage = 1;
}

// ==========================
// OFFSET
// ==========================

$offset = ($currentPage - 1) * $recordsPerPage;

// ==========================
// GET STAFF RECORDS
// ==========================

$sql = "
    SELECT *
    FROM staffs
    $whereSql
    ORDER BY last_name ASC, first_name ASC, middle_name ASC
    LIMIT $recordsPerPage OFFSET $offset
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Staff query prepare failed: " . mysqli_error($conn));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

// ==========================
// PAGINATION QUERY PARAMETERS
// ==========================

$queryParams = [];

if ($search !== '') {
    $queryParams['search'] = $search;
}

if ($department !== '' && $department !== 'all') {
    $queryParams['department'] = $department;
}

if ($position !== '' && $position !== 'all') {
    $queryParams['position'] = $position;
}

function pageUrl($page, $queryParams) {

    $queryParams['page'] = $page;

    return '?' . http_build_query($queryParams);
}

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
    </header>

    <!-- FILTER + SEARCH -->
    <section class="filter-section">

        <form class="search-box" method="GET" action="staffs.php">

            <i class='bx bx-search'></i>

            <input
                type="text"
                name="search"
                id="staffSearch"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search by ID, Name, Sex, Position, Designation, or Department..."
            >

            <?php if ($department !== '' && $department !== 'all'): ?>
                <input
                    type="hidden"
                    name="department"
                    value="<?= htmlspecialchars($department) ?>"
                >
            <?php endif; ?>

            <?php if ($position !== '' && $position !== 'all'): ?>
                <input
                    type="hidden"
                    name="position"
                    value="<?= htmlspecialchars($position) ?>"
                >
            <?php endif; ?>

        </form>

        <div class="filters">

            <div class="filter-group">
                <label for="department">Department</label>
                <select id="department" class="filter-select">

                    <option value="all"
                        <?= $department === '' || $department === 'all' ? 'selected' : '' ?>>
                        All Departments
                    </option>

                    <option value="technology"
                        <?= $department === 'technology' ? 'selected' : '' ?>>
                        Technology
                    </option>

                    <option value="education"
                        <?= $department === 'education' ? 'selected' : '' ?>>
                        Education
                    </option>

                    <option value="business"
                        <?= $department === 'business' ? 'selected' : '' ?>>
                        Business
                    </option>

                    <option value="clinic"
                        <?= $department === 'clinic' ? 'selected' : '' ?>>
                        Clinic
                    </option>

                    <option value="library"
                        <?= $department === 'library' ? 'selected' : '' ?>>
                        Library
                    </option>

                    <option value="guidance"
                        <?= $department === 'guidance' ? 'selected' : '' ?>>
                        Guidance Office
                    </option>

                    <option value="cashier"
                        <?= $department === 'cashier' ? 'selected' : '' ?>>
                        Cashier
                    </option>

                    <option value="registrar"
                        <?= $department === 'registrar' ? 'selected' : '' ?>>
                        Registrar
                    </option>

                    <option value="utility"
                        <?= $department === 'utility' ? 'selected' : '' ?>>
                        Utility
                    </option>

                    <option value="admin"
                        <?= $department === 'admin' ? 'selected' : '' ?>>
                        Admin Office
                    </option>

                    <option value="saso"
                        <?= $department === 'saso' ? 'selected' : '' ?>>
                        SASO
                    </option>

                    <option value="csu"
                        <?= $department === 'csu' ? 'selected' : '' ?>>
                        CSU
                    </option>

                </select>
            </div>

            <div class="filter-group">
                <label for="position">Position</label>
                <select id="position" class="filter-select">

                    <option value="all"
                        <?= $position === '' || $position === 'all' ? 'selected' : '' ?>>
                        All Positions
                    </option>

                    <option value="teaching staff"
                        <?= $position === 'teaching staff' ? 'selected' : '' ?>>
                        Teaching Staff
                    </option>

                    <option value="non-teaching staff"
                        <?= $position === 'non-teaching staff' ? 'selected' : '' ?>>
                        Non-Teaching Staff
                    </option>

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

                    <?php if (mysqli_num_rows($result) > 0): ?>

                    <?php while ($staff = mysqli_fetch_assoc($result)) : ?>

<tr>

    <td>
        <strong><?= htmlspecialchars($staff['staff_id_number']); ?></strong>
    </td>

    <td>
        <?= htmlspecialchars(
            trim($staff['last_name'] ?? '') . ', ' .
            trim($staff['first_name'] ?? '') . ' ' .
            trim($staff['middle_name'] ?? '')
        ); ?>
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

                    id: <?= (int) $staff["id"] ?>,
                    staff_id_number: <?= json_encode($staff["staff_id_number"]) ?>,
                    last_name: <?= json_encode($staff["last_name"]) ?>,
                    first_name: <?= json_encode($staff["first_name"]) ?>,
                    middle_name: <?= json_encode($staff["middle_name"] ?? "") ?>,
                    dob: <?= json_encode($staff["date_of_birth"]) ?>,
                    age: <?= json_encode(date_diff(date_create($staff["date_of_birth"]), date_create("today"))->y) ?>,

                    sex: <?= json_encode($staff["sex"]) ?>,
                    position: <?= json_encode($staff["position"]) ?>,
                    designation: <?= json_encode($staff["designation"]) ?>,
                    department: <?= json_encode($staff["department"]) ?>,

                    contact: <?= json_encode($staff["contact_number"]) ?>,
                    address: <?= json_encode($staff["address"]) ?>,

                    blood: <?= json_encode((string)$staff["blood_type"]) ?>,
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
            <?php if ($role !== 'Clinic Staff'): ?>
            <button
                type="button"
                class="icon-btn edit"
                title="Edit"
                onclick="window.location.href='edit_staff.php?id=<?= (int) $staff['id'] ?>'">
                <i class="bx bx-edit"></i>
            </button>
            <?php endif; ?>

            <?php if ($role !== 'Clinic Staff'): ?>
                <!-- Archive Button -->
                <button
                    type="button"
                    class="icon-btn archive"
                    title="Archive"
                    onclick='openArchiveStaffModal({
                        id: <?= json_encode($staff["staff_id_number"]) ?>,
                        first_name: <?= json_encode($staff["first_name"] ?? "") ?>,
                        middle_name: <?= json_encode($staff["middle_name"] ?? "") ?>,
                        last_name: <?= json_encode($staff["last_name"] ?? "") ?>
                    })'>
                    <i class="bx bx-archive"></i>
                </button>
            <?php endif; ?>

        </div>

    </td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
    <td colspan="7" style="text-align: center; padding: 30px;">
        <?= ($search !== '' || ($department !== '' && $department !== 'all') || ($position !== '' && $position !== 'all'))
            ? 'No staff records found. Try adjusting your search or filters.'
            : 'No staff records yet.' ?>
    </td>
</tr>

<?php endif; ?>

                </tbody>
            </table>
                <!-- TABLE FOOTER -->
                <div class="table-footer">

                    <?php
                    $displayPage = max(1, (int) $currentPage);
                    $displayLimit = max(1, (int) $recordsPerPage);
                    $displayTotal = max(0, (int) $totalRecords);

                    $showingFrom = $displayTotal > 0
                        ? (($displayPage - 1) * $displayLimit + 1)
                        : 0;

                    $showingTo = min($displayPage * $displayLimit, $displayTotal);
                    ?>

                    <p>
                        Showing
                        <?= $showingFrom ?>
                        -
                        <?= $showingTo ?>
                        of
                        <?= $displayTotal ?>
                        <?= $displayTotal === 1 ? 'record' : 'records' ?>
                    </p>

                    <?php if ($totalPages > 1): ?>

                    <div class="pagination">

                        <!-- PREVIOUS -->
                        <?php if ($currentPage > 1): ?>

                            <a
                                href="<?= pageUrl($currentPage - 1, $queryParams) ?>"
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

                            <a
                                href="<?= pageUrl($page, $queryParams) ?>"
                                class="btn-page <?= $page == $currentPage ? 'active' : '' ?>"
                            >
                                <?= $page ?>
                            </a>

                        <?php endfor; ?>


                        <!-- NEXT -->
                        <?php if ($currentPage < $totalPages): ?>

                            <a
                                href="<?= pageUrl($currentPage + 1, $queryParams) ?>"
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

                    <button
                        type="button"
                        class="btn-view-history"
                        onclick="openStaffMedicalHistoryModal()">

                        <i class="bx bx-file-find"></i>
                        <span>View All Medical History</span>
                        <i class="bx bx-chevron-right history-link-arrow"></i>

                    </button>
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

<!-- STAFF MEDICAL HISTORY MODAL -->
<div id="staffMedicalHistoryModal" class="modal">
    <div class="modal-content modal-container medical-history-mode">

        <span class="close" onclick="closeStaffMedicalHistoryModal()">&times;</span>

        <div class="medical-history-header">
            <div class="history-header-icon">
                <i class="bx bx-file-medical"></i>
            </div>

            <div class="history-header-text">
                <span>STAFF RECORD</span>
                <h1>Medical History</h1>
                <p id="historyStaffName">Staff Name</p>
            </div>
        </div>

        <div class="medical-history-layout">

            <!-- PAST MEDICAL HISTORY -->
            <div class="info-section past-history-section">
                <div class="info-title">Past Medical History</div>

                <div class="history-content">
                    <p id="pastStaffMedicalHistory">
                        No past medical history recorded.
                    </p>
                </div>
            </div>

            <!-- CLINIC HISTORY -->
            <div class="info-section clinic-history-section">
                <div class="info-title">Clinic History</div>

                <div class="history-subsection">
                    <h3>
                        <i class="bx bx-notepad"></i>
                        Clinic History
                    </h3>

                    <div id="staffConsultationHistoryList">
                        <p>No consultation records found.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ARCHIVE STAFF CONFIRMATION MODAL -->
<div id="archiveStaffModal" class="modal">
    <div class="modal-content modal-container archive-mode">

        <input type="hidden" id="archiveStaffId">

        <div class="archive-icon">
            <i class="bx bx-archive"></i>
        </div>

        <h2>Archive Staff?</h2>

        <p class="archive-description">
            Are you sure you want to archive
            <strong id="archiveStaffName">this staff member</strong>?
        </p>

        <div class="archive-notice">
            <i class="bx bx-info-circle"></i>
            <p>
                This staff member will be moved to Archived Records.
                You can restore the record later.
            </p>
        </div>

        <div class="modal-actions">
            <button
                type="button"
                class="btn-secondary"
                onclick="closeArchiveStaffModal()">
                Cancel
            </button>

            <button
                type="button"
                class="btn-archive"
                id="confirmArchiveStaffBtn"
                onclick="confirmArchiveStaff()">
                Archive Staff
            </button>
        </div>

    </div>
</div>

<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>

<div id="messageModal" class="modal active">

    <div class="modal-content message-modal">

        <div class="message-icon <?= isset($_SESSION['success']) ? 'success' : 'error'; ?>">

            <?php if (isset($_SESSION['success'])): ?>

                <i class='bx bx-check'></i>

            <?php else: ?>

                <i class='bx bx-x-circle'></i>

            <?php endif; ?>

        </div>

        <h2>
            <?= isset($_SESSION['success'])
                ? $_SESSION['success']
                : "Action Failed!"; ?>
        </h2>

        <p>
            <?= isset($_SESSION['success_message'])
                ? $_SESSION['success_message']
                : (isset($_SESSION['success'])
                    ? "The action was completed successfully."
                    : $_SESSION['error']); ?>
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
unset($_SESSION['success_message']);
unset($_SESSION['error']);
?>

<?php endif; ?>

<!-- SCRIPTS -->
<script src="../includes/includes.js"></script>
<script src="../assets/js/staffs.js"></script>

</body>
</html>