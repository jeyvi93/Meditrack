<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['School Nurse', 'Clinic Staff'], true)) {
    if ($role === 'Administrator') {
        header("Location: ../admin/accounts.php");
    } else {
        header("Location: ../patient/view_consultation.php");
    }
    exit();
}

require_once "../config/database.php";

// ==========================
// SEARCH + FILTER + PAGINATION
// ==========================

$recordsPerPage = 15;

$currentPage = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

// ==========================
// SEARCH
// ==========================

$search = trim($_GET['search'] ?? '');

// ==========================
// ROLE FILTER
// ==========================

$roleFilter = trim($_GET['role'] ?? '');

if (!in_array($roleFilter, ['', 'School Nurse', 'Clinic Staff'], true)) {
    $roleFilter = '';
}

// ==========================
// MODULE FILTER
// ==========================

$moduleFilter = trim($_GET['module'] ?? '');

$allowedModules = [
    '',
    'Appointments',
    'Medicine',
    'Students',
    'Staffs',
    'Referrals'
];

if (!in_array($moduleFilter, $allowedModules, true)) {
    $moduleFilter = '';
}

// ==========================
// DATE FILTER
// ==========================

$dateFilter = trim($_GET['date'] ?? '');

$dateObject = DateTime::createFromFormat('!Y-m-d', $dateFilter);

if (
    $dateFilter !== '' &&
    (!$dateObject || $dateObject->format('Y-m-d') !== $dateFilter)
) {
    $dateFilter = '';
}

// ==========================
// BUILD WHERE CONDITION
// ==========================

$where = [];
$params = [];
$types = "";

if ($search !== '') {

    $where[] = "(
        user_name LIKE ?
        OR user_role LIKE ?
        OR action_type LIKE ?
        OR module LIKE ?
        OR description LIKE ?
    )";

    $searchParam = "%" . $search . "%";

    for ($i = 0; $i < 5; $i++) {
        $params[] = $searchParam;
    }

    $types .= "sssss";
}

if ($roleFilter !== '') {

    $where[] = "user_role = ?";

    $params[] = $roleFilter;
    $types .= "s";
}

if ($moduleFilter !== '') {

    $where[] = "module = ?";

    $params[] = $moduleFilter;
    $types .= "s";
}

if ($dateFilter !== '') {

    $where[] = "DATE(created_at) = ?";

    $params[] = $dateFilter;
    $types .= "s";
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

// ==========================
// COUNT FILTERED RECORDS
// ==========================

$countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM clinic_activity_logs
    $whereSql
");

if (!$countStmt) {
    die("Count query failed: " . $conn->error);
}

if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();

$totalRecords = (int) $countStmt->get_result()
    ->fetch_assoc()['total'];

$countStmt->close();

// ==========================
// TOTAL PAGES
// ==========================

$totalPages = (int) ceil($totalRecords / $recordsPerPage);

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
// GET ACTIVITY RECORDS
// ==========================

$sql = "
    SELECT
        user_name,
        user_role,
        action_type,
        module,
        description,
        created_at
    FROM clinic_activity_logs
    $whereSql
    ORDER BY created_at DESC, id DESC
    LIMIT $recordsPerPage OFFSET $offset
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Activity query failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

// ==========================
// PAGINATION QUERY PARAMETERS
// ==========================

$queryParams = [];

if ($search !== '') {
    $queryParams['search'] = $search;
}

if ($roleFilter !== '') {
    $queryParams['role'] = $roleFilter;
}

if ($moduleFilter !== '') {
    $queryParams['module'] = $moduleFilter;
}

if ($dateFilter !== '') {
    $queryParams['date'] = $dateFilter;
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

    <title>Clinic Activity Log | MediTrack</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/view_consultation.css">
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
                <h1>Clinic Activity Log</h1>
                <p>Review important activities performed by clinic personnel.</p>
            </div>

        </header>

        <!-- ACTIVITY LOGS CARD -->
        <section class="logs-card">

            <!-- TOOLBAR -->
            <div class="logs-toolbar">

                <!-- SEARCH -->
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>

                    <input
                        type="text"
                        id="activitySearch"
                        placeholder="Search personnel, action, or details..."
                    >
                </div>

                <!-- FILTERS -->
                <div class="filter-group">

                    <select class="filter-select" id="roleFilter">
                        <option value="">All Personnel</option>
                        <option value="School Nurse">School Nurse</option>
                        <option value="Clinic Staff">Clinic Staff</option>
                    </select>

                    <input
                        type="date"
                        class="filter-select"
                        id="dateFilter"
                    >

                </div>

            </div>

            <!-- TABLE -->
            <div class="table-responsive">

                <table class="logs-table">

                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Personnel</th>
                            <th>Role</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($row = $result->fetch_assoc()): ?>

                                <tr>

                                    <!-- DATE & TIME -->
                                    <td class="date-cell">

                                        <strong>
                                            <?= date(
                                                "M d, Y",
                                                strtotime($row['created_at'])
                                            ) ?>
                                        </strong>

                                        <span>
                                            <?= date(
                                                "h:i A",
                                                strtotime($row['created_at'])
                                            ) ?>
                                        </span>

                                    </td>

                                    <!-- PERSONNEL -->
                                    <td>
                                        <span class="patient-name">
                                            <?= htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>

                                    <!-- ROLE -->
                                    <td>

                                        <span <?= $row['user_role'] === 'School Nurse' ? 'pill-student' : 'pill-staff' ?>">
                                            <?= htmlspecialchars($row['user_role'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                    </td>

                                    <!-- MODULE -->
                                    <td>
                                        <?= htmlspecialchars($row['module'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <!-- ACTION -->
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($row['action_type'], ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                    </td>

                                    <!-- DESCRIPTION -->
                                    <td>
                                        <?= htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" style="text-align:center;">
                                    No clinic activity records found.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- TABLE FOOTER -->
            <div class="table-footer">

                <?php
                $showingFrom = $totalRecords > 0
                    ? (($currentPage - 1) * $recordsPerPage + 1)
                    : 0;

                $showingTo = min(
                    $currentPage * $recordsPerPage,
                    $totalRecords
                );
                ?>

                <p>
                    Showing
                    <?= $showingFrom ?>
                    -
                    <?= $showingTo ?>
                    of
                    <?= $totalRecords ?>
                    <?= $totalRecords === 1 ? 'record' : 'records' ?>
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

                            <button class="btn-page" disabled>
                                <i class="bx bx-chevron-left"></i>
                            </button>

                        <?php endif; ?>

                        <!-- PAGE NUMBERS -->
                        <?php for ($page = 1; $page <= $totalPages; $page++): ?>

                            <a
                                href="<?= pageUrl($page, $queryParams) ?>"
                                class="btn-page <?= $page === $currentPage ? 'active' : '' ?>"
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

                            <button class="btn-page" disabled>
                                <i class="bx bx-chevron-right"></i>
                            </button>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

    <?php
    $stmt->close();
    $conn->close();
    ?>

    <script src="../assets/js/clinic_activity_logs.js"></script>
    <script src="../includes/includes.js"></script>

</body>
</html>