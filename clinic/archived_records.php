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

// Only authorized clinic management users may access Archived Records.
if ($role === 'Clinic Staff') {
    header("Location: dashboard.php");
    exit();
}

// ==========================
// TAB SELECTION
// ==========================

$tab = $_GET['tab'] ?? 'students';

if (!in_array($tab, ['students', 'staffs'], true)) {
    $tab = 'students';
}

// ==========================
// SEARCH + PAGINATION
// ==========================

$recordsPerPage = 20;

$currentPage = filter_var(
    $_GET['page'] ?? 1,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$currentPage = ($currentPage !== false) ? $currentPage : 1;

$search = trim($_GET['search'] ?? '');

$params = [];
$types = '';
$where = ['is_archived = 1'];

if ($search !== '') {
    $searchParam = '%' . $search . '%';

    if ($tab === 'students') {
        $where[] = "(
            student_id_number LIKE ?
            OR last_name LIKE ?
            OR first_name LIKE ?
            OR middle_name LIKE ?
            OR sex LIKE ?
        )";

        $params = array_fill(0, 5, $searchParam);
        $types = 'sssss';
    } else {
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

        $params = array_fill(0, 8, $searchParam);
        $types = 'ssssssss';
    }
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

// ==========================
// SELECT TABLE
// ==========================

$table = $tab === 'students' ? 'students' : 'staffs';

// ==========================
// COUNT ARCHIVED RECORDS
// ==========================

$countSql = "SELECT COUNT(*) AS total FROM $table $whereSql";

$countStmt = mysqli_prepare($conn, $countSql);

if (!$countStmt) {
    die("Count query failed.");
}

if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}

mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);
$totalRecords = (int) mysqli_fetch_assoc($countResult)['total'];

mysqli_stmt_close($countStmt);

// ==========================
// PAGINATION
// ==========================

$totalRecords = (int) $totalRecords;
$recordsPerPage = 20;
$currentPage = (int) $currentPage;

$totalPages = (int) ceil($totalRecords / $recordsPerPage);

if ($totalPages > 0 && $currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $recordsPerPage;

// ==========================
// GET ARCHIVED RECORDS
// ==========================

$sql = "
    SELECT *
    FROM $table
    $whereSql
    ORDER BY last_name ASC, first_name ASC, middle_name ASC
    LIMIT $recordsPerPage OFFSET $offset
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Archived records query failed.");
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// ==========================
// PAGINATION URL
// ==========================

function archivedPageUrl($page, $tab, $search) {
    return '?' . http_build_query([
        'tab' => $tab,
        'search' => $search,
        'page' => $page
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Records | MediTrack</title>

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/staffs.css">
    <link rel="stylesheet" href="../assets/css/archived_records.css">

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<main class="main-content">

    <!-- PAGE HEADER -->
    <header class="module-header">
        <div class="title-area">
            <h1>Archived Records</h1>
            <p>View and restore archived student and staff profiles.</p>
        </div>
    </header>

    <!-- SEARCH + TABS -->
    <section class="filter-section">

        <form class="search-box" method="GET" action="archived_records.php">
            <i class="bx bx-search"></i>

            <input
                type="text"
                name="search"
                id="archiveSearch"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search archived records..."
            >

            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
        </form>

        <div class="archive-tabs">
            <a
                href="?tab=students"
                class="archive-tab <?= $tab === 'students' ? 'active' : '' ?>"
            >
                <i class="bx bx-user"></i> Students
            </a>

            <a
                href="?tab=staffs"
                class="archive-tab <?= $tab === 'staffs' ? 'active' : '' ?>"
            >
                <i class="bx bx-group"></i> Staffs
            </a>
        </div>

    </section>

    <!-- ARCHIVED RECORDS TABLE -->
    <section class="table-wrapper">

        <table>
            <thead>
                <?php if ($tab === 'students'): ?>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Sex</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th>Staff ID</th>
                        <th>Full Name</th>
                        <th>Sex</th>
                        <th>Position</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                <?php endif; ?>
            </thead>

            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($record = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <?php
                            $fullName = trim(
                                ($record['last_name'] ?? '') . ', ' .
                                ($record['first_name'] ?? '') . ' ' .
                                ($record['middle_name'] ?? '')
                            );
                            ?>

                            <?php if ($tab === 'students'): ?>
                                <td><?= htmlspecialchars($record['student_id_number'] ?? '') ?></td>
                                <td><?= htmlspecialchars($fullName) ?></td>
                                <td><?= htmlspecialchars($record['sex'] ?? '') ?></td>
                                <td><?= htmlspecialchars($record['course'] ?? '') ?></td>
                                <td>
                                    <?php
                                    $year = (int) ($record['year_level'] ?? 0);

                                    if ($year > 0) {
                                        echo $year;

                                        if ($year == 1)
                                            echo "st Year";
                                        elseif ($year == 2)
                                            echo "nd Year";
                                        elseif ($year == 3)
                                            echo "rd Year";
                                        else
                                            echo "th Year";
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($record['department'] ?? '') ?></td>
                            <?php else: ?>
                                <td><?= htmlspecialchars($record['staff_id_number'] ?? '') ?></td>
                                <td><?= htmlspecialchars($fullName) ?></td>
                                <td><?= htmlspecialchars($record['sex'] ?? '') ?></td>
                                <td><?= htmlspecialchars($record['position'] ?? '') ?></td>
                                <td><?= htmlspecialchars($record['designation'] ?? '') ?></td>
                                <td><?= htmlspecialchars($record['department'] ?? '') ?></td>
                            <?php endif; ?>

                            <td>

                                <?php if ($tab === 'students'): ?>

                                    <button
                                        type="button"
                                        class="icon-btn view"
                                        title="View"
                                        onclick='openStudentModal({
                                            id: <?= json_encode($record["student_id_number"]) ?>,
                                            first_name: <?= json_encode($record["first_name"] ?? "") ?>,
                                            middle_name: <?= json_encode($record["middle_name"] ?? "") ?>,
                                            last_name: <?= json_encode($record["last_name"] ?? "") ?>,
                                            dob: <?= json_encode($record["date_of_birth"] ?? "") ?>,
                                            age: <?= json_encode(
                                                !empty($record["date_of_birth"])
                                                    ? date_diff(
                                                        date_create($record["date_of_birth"]),
                                                        date_create("today")
                                                    )->y
                                                    : ""
                                            ) ?>,
                                            sex: <?= json_encode($record["sex"] ?? "") ?>,
                                            course: <?= json_encode($record["course"] ?? "") ?>,
                                            year: <?= json_encode(
                                                $record["year_level"] == 1 ? "1st Year" :
                                                ($record["year_level"] == 2 ? "2nd Year" :
                                                ($record["year_level"] == 3 ? "3rd Year" :
                                                $record["year_level"] . "th Year"))
                                            ) ?>,
                                            department: <?= json_encode($record["department"] ?? "") ?>,
                                            contact: <?= json_encode($record["contact_number"] ?? "") ?>,
                                            address: <?= json_encode($record["address"] ?? "") ?>,
                                            blood: <?= json_encode($record["blood_type"] ?? "") ?>,
                                            height: <?= json_encode($record["height"] ?? null) ?>,
                                            weight: <?= json_encode($record["weight"] ?? null) ?>,
                                            allergies: <?= json_encode($record["allergies"] ?? null) ?>,
                                            history: <?= json_encode($record["medical_history"] ?? null) ?>,
                                            medications: <?= json_encode($record["current_medications"] ?? null) ?>,
                                            vaccines: <?= json_encode($record["vaccines"] ?? null) ?>,
                                            emergencyName: <?= json_encode($record["emergency_name"] ?? "") ?>,
                                            relationship: <?= json_encode($record["emergency_relationship"] ?? "") ?>,
                                            emergencyNumber: <?= json_encode($record["emergency_contact"] ?? "") ?>,
                                            philhealth: <?= json_encode($record["philhealth_number"] ?? null) ?>,
                                            insuranceNumber: <?= json_encode($record["insurance_number"] ?? null) ?>,
                                            insuranceCompany: <?= json_encode($record["insurance_company"] ?? null) ?>
                                        })'>

                                        <i class="bx bx-show"></i>
                                    </button>

                                <?php else: ?>

                                    <!-- ARCHIVED STAFF VIEW BUTTON -->
                                    <button
                                        type="button"
                                        class="icon-btn view"
                                        title="View"
                                        onclick='openArchivedStaffModal({

                                            id: <?= json_encode($record["staff_id_number"] ?? "") ?>,
                                            first_name: <?= json_encode($record["first_name"] ?? "") ?>,
                                            middle_name: <?= json_encode($record["middle_name"] ?? "") ?>,
                                            last_name: <?= json_encode($record["last_name"] ?? "") ?>,
                                            dob: <?= json_encode($record["date_of_birth"] ?? "") ?>,

                                            age: <?= json_encode(
                                                !empty($record["date_of_birth"])
                                                    ? date_diff(
                                                        date_create($record["date_of_birth"]),
                                                        date_create("today")
                                                    )->y
                                                    : ""
                                            ) ?>,

                                            sex: <?= json_encode($record["sex"] ?? "") ?>,
                                            position: <?= json_encode($record["position"] ?? "") ?>,
                                            designation: <?= json_encode($record["designation"] ?? "") ?>,
                                            department: <?= json_encode($record["department"] ?? "") ?>,

                                            contact: <?= json_encode($record["contact_number"] ?? "") ?>,
                                            address: <?= json_encode($record["address"] ?? "") ?>,

                                            blood: <?= json_encode($record["blood_type"] ?? "") ?>,
                                            height: <?= json_encode($record["height"] ?? null) ?>,
                                            weight: <?= json_encode($record["weight"] ?? null) ?>,
                                            allergies: <?= json_encode($record["allergies"] ?? null) ?>,
                                            history: <?= json_encode($record["medical_history"] ?? null) ?>,
                                            medications: <?= json_encode($record["current_medications"] ?? null) ?>,
                                            vaccines: <?= json_encode($record["vaccines"] ?? null) ?>,

                                            philhealth: <?= json_encode($record["philhealth_number"] ?? null) ?>,
                                            insuranceNumber: <?= json_encode($record["insurance_number"] ?? null) ?>,
                                            insuranceCompany: <?= json_encode($record["insurance_company"] ?? null) ?>,

                                            emergencyName: <?= json_encode($record["emergency_name"] ?? "") ?>,
                                            relationship: <?= json_encode($record["emergency_relationship"] ?? "") ?>,
                                            emergencyNumber: <?= json_encode($record["emergency_contact"] ?? "") ?>

                                        })'>

                                        <i class="bx bx-show"></i>
                                    </button>

                            <?php endif; ?>
                                <form
                                    action="../actions/restore_record.php"
                                    method="POST"
                                    style="display:inline;"
                                >
                                    <input type="hidden" name="id"
                                        value="<?= (int) $record['id'] ?>">
                                    <input type="hidden" name="type"
                                        value="<?= htmlspecialchars($tab) ?>">

                                    <button type="submit" class="icon-btn restore" title="Restore">
                                        <i class="bx bx-undo"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $tab === 'students' ? 7 : 7 ?>"
                            style="text-align:center; padding:25px;">
                            No archived <?= $tab === 'students' ? 'students' : 'staff records' ?> found.
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
                            href="<?= htmlspecialchars(archivedPageUrl($currentPage - 1, $tab, $search), ENT_QUOTES, 'UTF-8') ?>"
                            class="btn-page"
                        >
                            <i class="bx bx-chevron-left"></i>
                        </a>

                    <?php else: ?>

                        <button class="btn-page" disabled>
                            <i class="bx bx-chevron-left"></i>
                        </button>

                    <?php endif; ?>


                    <!-- PAGE NUMBERS -->
                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>

                        <a
                            href="<?= htmlspecialchars(archivedPageUrl($page, $tab, $search), ENT_QUOTES, 'UTF-8') ?>"
                            class="btn-page <?= $page === $currentPage ? 'active' : '' ?>"
                        >
                            <?= $page ?>
                        </a>

                    <?php endfor; ?>


                    <!-- NEXT -->
                    <?php if ($currentPage < $totalPages): ?>

                        <a
                            href="<?= htmlspecialchars(archivedPageUrl($currentPage + 1, $tab, $search), ENT_QUOTES, 'UTF-8') ?>"
                            class="btn-page"
                        >
                            <i class="bx bx-chevron-right"></i>
                        </a>

                    <?php else: ?>

                        <button class="btn-page" disabled>
                            <i class="bx bx-chevron-right"></i>
                        </button>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div>

    </section>

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

    <!-- VIEW ARCHIVED STAFF MODAL -->
    <div id="archivedStaffModal" class="modal">
        <div class="modal-content modal-container view-mode">

            <span class="close" onclick="closeArchivedStaffModal()">&times;</span>

            <!-- HEADER SUMMARY -->
            <div class="staff-summary">

                <div class="profile-avatar p-avatar">
                    <i class='bx bx-user'></i>
                </div>

                <div class="summary-info">
                    <h1 id="as_fullName">Full Name</h1>
                    <p id="as_staffId">Staff ID</p>
                </div>

            </div>

            <!-- PERSONAL INFORMATION -->
            <div class="info-section">
                <div class="info-title">Personal Information</div>

                <div class="info-grid">

                    <div class="form-group">
                        <label>Date of Birth</label>
                        <p id="as_dob">--</p>
                    </div>

                    <div class="form-group">
                        <label>Age</label>
                        <p id="as_age">--</p>
                    </div>

                    <div class="form-group">
                        <label>Sex</label>
                        <p id="as_sex">--</p>
                    </div>

                    <div class="form-group">
                        <label>Position</label>
                        <p id="as_position">--</p>
                    </div>

                    <div class="form-group">
                        <label>Designation</label>
                        <p id="as_designation">--</p>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <p id="as_department">--</p>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <p id="as_contact">--</p>
                    </div>

                    <div class="form-group full-width">
                        <label>Address</label>
                        <p id="as_address">--</p>
                    </div>

                </div>
            </div>

            <!-- HEALTH INFORMATION -->
            <div class="info-section">
                <div class="info-title">Health Information</div>

                <div class="info-grid">

                    <div class="form-group">
                        <label>Blood Type</label>
                        <p id="as_blood">--</p>
                    </div>

                    <div class="form-group">
                        <label>Height</label>
                        <p id="as_height">--</p>
                    </div>

                    <div class="form-group">
                        <label>Weight</label>
                        <p id="as_weight">--</p>
                    </div>

                    <div class="form-group">
                        <label>Allergies</label>
                        <p id="as_allergies">--</p>
                    </div>

                    <div class="form-group">
                        <label>Medical History</label>
                        <p id="as_history">--</p>
                    </div>

                    <div class="form-group">
                        <label>Medications</label>
                        <p id="as_medications">--</p>
                    </div>

                    <div class="form-group full-width">
                        <label>Vaccines</label>
                        <p id="as_vaccines">--</p>
                    </div>

                </div>
            </div>

            <!-- INSURANCE INFORMATION -->
            <div class="info-section">

                <div class="info-title">Insurance Information</div>

                <div class="info-grid">

                    <div class="form-group">
                        <label>PhilHealth Number</label>
                        <p id="as_philhealth">--</p>
                    </div>

                    <div class="form-group">
                        <label>Insurance Number</label>
                        <p id="as_insuranceNumber">--</p>
                    </div>

                    <div class="form-group full-width">
                        <label>Insurance Company</label>
                        <p id="as_insuranceCompany">--</p>
                    </div>

                </div>
            </div>

            <!-- EMERGENCY CONTACT -->
            <div class="info-section">
                <div class="info-title">Emergency Contact</div>

                <div class="info-grid">

                    <div class="form-group">
                        <label>Name</label>
                        <p id="as_emergencyName">--</p>
                    </div>

                    <div class="form-group">
                        <label>Relationship</label>
                        <p id="as_relationship">--</p>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <p id="as_emergencyNumber">--</p>
                    </div>

                </div>
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
                ? htmlspecialchars($_SESSION['success'])
                : "Action Failed!"; ?>
        </h2>

        <p>
            <?= htmlspecialchars(
                $_SESSION['success_message']
                    ?? $_SESSION['error']
                    ?? "The action was completed successfully."
            ); ?>
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
</main>

<script src="../assets/js/archived_records.js"></script>
<script src="../includes/includes.js"></script>
</body>
</html>

