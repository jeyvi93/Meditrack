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

if (($_SESSION['role'] ?? '') !== 'School Nurse') {
    header("Location: ../index.php");
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
// BUILD WHERE CONDITION
// ==========================

$where = ["is_archived = 0"];
$params = [];
$types = "";

if ($search !== '') {

    $where[] = "(
        student_id_number LIKE ?
        OR first_name LIKE ?
        OR middle_name LIKE ?
        OR last_name LIKE ?
        OR philhealth_number LIKE ?
    )";

    $searchParam = "%" . $search . "%";

    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;

    $types .= "sssss";
}

if ($department !== '') {

    $where[] = "department = ?";

    $params[] = $department;

    $types .= "s";
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

// ==========================
// COUNT FILTERED RECORDS
// ==========================

$countSql = "SELECT COUNT(*) AS total FROM students $whereSql";

$countStmt = mysqli_prepare($conn, $countSql);

if (!$countStmt) {
    die("Count prepare failed: " . mysqli_error($conn));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}

mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);

$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = (int) ($countRow['total'] ?? 0);

mysqli_stmt_close($countStmt);

$totalPages = (int) ceil($totalRecords / $recordsPerPage);

// ==========================
// OFFSET
// ==========================

$offset = ($currentPage - 1) * $recordsPerPage;

// ==========================
// GET STUDENT RECORDS
// ==========================

$sql = "
    SELECT *
    FROM students
    $whereSql
    ORDER BY last_name ASC, first_name ASC, middle_name ASC
    LIMIT $recordsPerPage OFFSET $offset
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Student query prepare failed: " . mysqli_error($conn));
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

if ($department !== '') {
    $queryParams['department'] = $department;
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
        <title>Student Profiles | MediTrack</title>

        <!-- GLOBAL CSS -->
        <link rel="stylesheet" href="../assets/css/sidebar.css">

        <!-- MODULE CSS -->
        <link rel="stylesheet" href="../assets/css/students.css">

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
                <h1>Student Profiles</h1>
                <p>Manage and monitor student medical records.</p>
            </div>
        </header>

        <!-- FILTER + SEARCH -->
        <section class="filter-section">

            <form class="search-box" method="GET" action="students.php">

                <i class='bx bx-search'></i>

                <input
                    type="text"
                    name="search"
                    id="studentSearch"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search by ID, Name, or PhilHealth..."
                >

                <?php if ($department !== ''): ?>
                    <input
                        type="hidden"
                        name="department"
                        value="<?= htmlspecialchars($department) ?>"
                    >
                <?php endif; ?>

            </form>

            <div class="department-filters">

                <span class="filter-label">Department:</span>

                <a
                    href="?page=1<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                    class="filter-pill <?= $department === '' ? 'active' : '' ?>"
                >
                    All
                </a>

                <a
                    href="?page=1&department=Technology<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                    class="filter-pill <?= $department === 'Technology' ? 'active' : '' ?>"
                >
                    Technology
                </a>

                <a
                    href="?page=1&department=Education<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                    class="filter-pill <?= $department === 'Education' ? 'active' : '' ?>"
                >
                    Education
                </a>

                <a
                    href="?page=1&department=Business<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
                    class="filter-pill <?= $department === 'Business' ? 'active' : '' ?>"
                >
                    Business
                </a>

            </div>

        </section>

        <!-- TABLE -->
        <div class="table-container">
            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th> Student ID</th>
                            <th> Full Name</th>
                            <th> Sex</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

<tbody>

<?php if (mysqli_num_rows($result) > 0): ?>

<?php while ($student = mysqli_fetch_assoc($result)) : ?>

<tr>

    <td>
        <strong><?php echo htmlspecialchars($student['student_id_number']); ?></strong>
    </td>

    <td>
        <?php
        $displayName = trim(
            ($student['last_name'] ?? '') . ', ' .
            ($student['first_name'] ?? '') . ' ' .
            ($student['middle_name'] ?? '')
        );

        echo htmlspecialchars($displayName);
        ?>
    </td>

    <td>
        <?php echo htmlspecialchars($student['sex']); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($student['course']); ?>
    </td>

    <td>
<?php
$year = $student['year_level'];

echo $year;

if ($year == 1)
    echo "st Year";
elseif ($year == 2)
    echo "nd Year";
elseif ($year == 3)
    echo "rd Year";
else
    echo "th Year";
?>
</td>

    <td>
        <?php echo htmlspecialchars($student['department']); ?>
    </td>

    <td>

<div class="action-buttons">

    <button
class="icon-btn view"
title="View"
onclick='openStudentModal({
    id: <?= (int) $student["id"] ?>,
    student_id_number: <?= json_encode($student["student_id_number"]) ?>,
    first_name: <?= json_encode($student["first_name"]) ?>,
    middle_name: <?= json_encode($student["middle_name"] ?? "") ?>,
    last_name: <?= json_encode($student["last_name"]) ?>,
    dob: <?= json_encode($student["date_of_birth"]) ?>,
    age: <?= json_encode(date_diff(date_create($student["date_of_birth"]), date_create("today"))->y) ?>,
    sex: <?= json_encode($student["sex"]) ?>,
    course: <?= json_encode($student["course"]) ?>,
    year: <?= json_encode(
    $student["year_level"] == 1 ? "1st Year" :
    ($student["year_level"] == 2 ? "2nd Year" :
    ($student["year_level"] == 3 ? "3rd Year" :
    $student["year_level"] . "th Year"))
) ?>,
    department: <?= json_encode($student["department"]) ?>,
    contact: <?= json_encode($student["contact_number"]) ?>,
    address: <?= json_encode($student["address"]) ?>,

    blood: <?= json_encode($student["blood_type"]) ?>,
    height: <?= json_encode($student["height"] ?? null) ?>,
    weight: <?= json_encode($student["weight"] ?? null) ?>,
    allergies: <?= json_encode($student["allergies"] ?? null) ?>,
    history: <?= json_encode($student["medical_history"] ?? null) ?>,
    medications: <?= json_encode($student["current_medications"] ?? null) ?>,
    vaccines: <?= json_encode($student["vaccines"] ?? null) ?>,

    emergencyName: <?= json_encode($student["emergency_name"]) ?>,
    relationship: <?= json_encode($student["emergency_relationship"]) ?>,
    emergencyNumber: <?= json_encode($student["emergency_contact"]) ?>,

    philhealth: <?= json_encode($student["philhealth_number"] ?? null) ?>,
    insuranceNumber: <?= json_encode($student["insurance_number"] ?? null) ?>,
    insuranceCompany: <?= json_encode($student["insurance_company"] ?? null) ?>,

})'>
    <i class="bx bx-show"></i>
</button>

<?php if ($role !== 'Clinic Staff'): ?>
<button
    type="button"
    class="icon-btn edit"
    title="Edit"
    onclick="window.location.href='edit_student.php?id=<?= (int) $student['id'] ?>'">
    <i class="bx bx-edit"></i>
</button>
<?php endif; ?>

            <?php if ($role !== 'Clinic Staff'): ?>

                <button
                    type="button"
                    class="icon-btn archive"
                    title="Archive"
                    onclick='openArchiveStudentModal({
                        id: <?= (int) $student["id"] ?>,
                        first_name: <?= json_encode($student["first_name"]) ?>,
                        middle_name: <?= json_encode($student["middle_name"] ?? "") ?>,
                        last_name: <?= json_encode($student["last_name"]) ?>
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
        <?= ($search !== '' || $department !== '')
            ? 'No student records found. Try adjusting your search or filter.'
            : 'No student records yet.' ?>
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

 

<!-- VIEW STUDENT MODAL -->
<div id="studentModal" class="modal">
    <div class="modal-content modal-container view-mode">

        <span class="close" onclick="closeStudentModal()">&times;</span>

        <!-- HEADER SUMMARY -->
        <div class="student-summary">

            <div class="profile-avatar p-avatar">
                <i class='bx bx-user'></i>
            </div>

            <div class="summary-info">
                <h1 id="m_fullName">Full Name</h1>
                <p id="m_studentId">Student ID</p>
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
                    <label>Course</label>
                    <p id="m_course">--</p>
                </div>

                <div class="form-group">
                    <label>Year Level</label>
                    <p id="m_year">--</p>
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
                        onclick="openMedicalHistoryModal()">
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

<!-- VIEW ALL MEDICAL HISTORY MODAL -->
<div id="medicalHistoryModal" class="modal">

    <div class="modal-content modal-container medical-history-mode">

        <span class="close" onclick="closeMedicalHistoryModal()">&times;</span>

        <!-- MEDICAL HISTORY HEADER -->
        <div class="medical-history-header">

            <div class="history-header-icon">
                <i class="bx bx-file-medical"></i>
            </div>

            <div class="history-header-text">
                <span>STUDENT RECORD</span>
                <h1>Medical History</h1>
                <p id="historyStudentName">Student Name</p>
            </div>

        </div>

        <!-- MEDICAL HISTORY LAYOUT -->
        <div class="medical-history-layout">

            <!-- LEFT: PAST MEDICAL HISTORY -->
            <div class="info-section past-history-section">

                <div class="info-title">Past Medical History</div>

                <div class="history-content">
                    <p id="pastMedicalHistory">
                        No past medical history recorded.
                    </p>
                </div>

            </div>

            <!-- RIGHT: CLINIC HISTORY -->
            <div class="info-section clinic-history-section">

                <div class="info-title">Clinic History</div>

                <!-- CONSULTATION HISTORY -->
                <div class="history-subsection">

                    <h3>
                        <i class="bx bx-notepad"></i>
                        Consultation History
                    </h3>

                    <div id="consultationHistoryList">
                        <p>No consultation records found.</p>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ARCHIVE STUDENT CONFIRMATION MODAL -->
<div id="archiveStudentModal" class="modal">
    <div class="modal-content modal-container archive-mode">

        <input type="hidden" id="archiveStudentId">

        <div class="archive-icon">
            <i class="bx bx-archive"></i>
        </div>

        <h2>Archive Student?</h2>

        <p class="archive-description">
            Are you sure you want to archive
            <strong id="archiveStudentName">this student</strong>?
        </p>

        <div class="archive-notice">
            <i class="bx bx-info-circle"></i>
            <p>
                The student will be moved to Archived Records.
                You can restore the record later.
            </p>
        </div>

        <div class="modal-actions">
            <button
                type="button"
                class="btn-secondary"
                onclick="closeArchiveStudentModal()">
                Cancel
            </button>

            <button
                type="button"
                class="btn-archive"
                id="confirmArchiveStudentBtn"
                onclick="confirmArchiveStudent()">
                Archive Student
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

    <script src="../includes/includes.js"></script>
    <script src="../assets/js/students.js"></script>

    
    </body>
    </html>