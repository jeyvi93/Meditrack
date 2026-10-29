<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

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
                                <td><?= htmlspecialchars($record['year_level'] ?? '') ?></td>
                            <?php else: ?>
                                <td><?= htmlspecialchars($record['staff_id_number'] ?? '') ?></td>
                                <td><?= htmlspecialchars($fullName) ?></td>
                                <td><?= htmlspecialchars($record['sex'] ?? '') ?></td>
                                <td><?= htmlspecialchars($record['position'] ?? '') ?></td>
                                <td><?= htmlspecialchars($record['designation'] ?? '') ?></td>
                                <td><?= htmlspecialchars($record['department'] ?? '') ?></td>
                            <?php endif; ?>

                            <td>
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
                        <td colspan="<?= $tab === 'students' ? 6 : 7 ?>"
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("archiveSearch");

    if (!searchInput) return;

    let searchTimeout;
    let searchController;

    searchInput.addEventListener("input", function () {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(async function () {
            const search = searchInput.value.trim();

            if (searchController) {
                searchController.abort();
            }

            searchController = new AbortController();

            const params = new URLSearchParams();

            params.set(
                "tab",
                new URLSearchParams(window.location.search).get("tab") || "students"
            );

            params.set("search", search);
            params.set("page", "1");

            try {
                const response = await fetch(
                    "archived_records.php?" + params.toString(),
                    { signal: searchController.signal }
                );

                if (!response.ok) {
                    throw new Error("Search request failed.");
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");

                const newTable = doc.querySelector(".table-wrapper table");
                const currentTable = document.querySelector(".table-wrapper table");

                const newFooter = doc.querySelector(".table-footer");
                const currentFooter = document.querySelector(".table-footer");

                if (newTable && currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                }

                if (newFooter && currentFooter) {
                    currentFooter.innerHTML = newFooter.innerHTML;
                }

                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set(
                    "tab",
                    params.get("tab")
                );
                newUrl.searchParams.set("search", search);
                newUrl.searchParams.set("page", "1");

                window.history.replaceState({}, "", newUrl);

            } catch (error) {
                if (error.name !== "AbortError") {
                    console.error("Archived records search error:", error);
                }
            }
        }, 300);
    });
});

function closeMessageModal() {
    const modal = document.getElementById("messageModal");

    if (modal) {
        modal.classList.remove("active");
    }
}
</script>
<script src="../includes/includes.js"></script>
</body>
</html>

