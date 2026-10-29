<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin-only access
if (($_SESSION['role'] ?? '') !== 'Administrator') {
    http_response_code(403);
    exit('Access denied. Admin privileges are required.');
}

require_once "../config/database.php";

// Search and filters
$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

// Pagination
$recordsPerPage = 20;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $recordsPerPage;

$searchLike = '%' . $search . '%';

// Count matching accounts
$countSql = "
    SELECT COUNT(*) AS total
    FROM users
    WHERE (? = '' OR full_name LIKE ? OR email LIKE ?)
      AND (? = '' OR role = ?)
";

$countStmt = mysqli_prepare($conn, $countSql);

mysqli_stmt_bind_param(
    $countStmt,
    "sssss",
    $search,
    $searchLike,
    $searchLike,
    $roleFilter,
    $roleFilter
);

mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);
$totalRecords = (int) mysqli_fetch_assoc($countResult)['total'];

mysqli_stmt_close($countStmt);

// Calculate pagination
$totalPages = max(
    1,
    (int) ceil($totalRecords / $recordsPerPage)
);

$page = min($page, $totalPages);
$offset = ($page - 1) * $recordsPerPage;

// Retrieve accounts
$sql = "
    SELECT
        users.id,
        users.full_name,
        users.email,
        users.role,
        users.email_verified,
        students.student_id_number,
        staffs.staff_id_number,
        COALESCE(
            students.student_id_number,
            staffs.staff_id_number
        ) AS account_id_number
    FROM users
    LEFT JOIN students ON users.student_id = students.id
    LEFT JOIN staffs ON users.staff_id = staffs.id
    WHERE (? = '' OR users.full_name LIKE ? OR users.email LIKE ?)
      AND (? = '' OR users.role = ?)
    ORDER BY users.id DESC
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "sssssii",
    $search,
    $searchLike,
    $searchLike,
    $roleFilter,
    $roleFilter,
    $recordsPerPage,
    $offset
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$accounts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $accounts[] = $row;
}

mysqli_stmt_close($stmt);

// Available roles for the filter
$rolesResult = mysqli_query(
    $conn,
    "SELECT DISTINCT role
     FROM users
     WHERE role IS NOT NULL AND role <> ''
     ORDER BY role ASC"
);

$roles = [];

while ($row = mysqli_fetch_assoc($rolesResult)) {
    $roles[] = $row['role'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account List | MediTrack</title>

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/accounts.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>

<!-- Static Admin Sidebar -->
<aside class="sidebar">

    <!-- LOGO -->
    <div class="logo">
        <img src="../assets/images/med.png" alt="Logo">
    </div>

    <!-- MENU -->
    <nav class="nav-menu">
        <p class="menu-label">Main Menu</p>

        <ul>
            <li>
                <a href="accounts.php" class="active">
                    <i class='bx bxs-user-detail'></i> Account List
                </a>
            </li>

            <li>
                <a href="create_personnel.php">
                    <i class='bx bxs-user-plus'></i> Create Personnel Account
                </a>
            </li>
        </ul>
    </nav>

    <!-- FOOTER -->
    <div class="sidebar-footer">

        <div class="admin-card" onclick="toggleLogoutMenu()">
            <img
                src="https://cdn-icons-png.flaticon.com/512/9131/9131590.png"
                alt="Administrator"
            >

            <div class="admin-info">
                <strong>
                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?>
                </strong>

                <small>Administrator</small>
            </div>

            <i class='bx bx-chevron-up-down'></i>
        </div>

        <!-- LOGOUT MENU -->
        <div id="logoutMenu" class="logout-menu">
            <button type="button" onclick="openLogoutModal()">
                <i class='bx bx-log-out'></i>
                Logout
            </button>
        </div>

    </div>

</aside>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal-overlay">
    <div class="logout-modal">

        <div class="logout-icon">
            <i class='bx bx-log-out-circle'></i>
        </div>

        <h2>Sign Out</h2>

        <p>
            Are you sure you want to sign out of your MediTrack account?
        </p>

        <div class="logout-buttons">
            <button
                type="button"
                class="logout-cancel"
                onclick="closeLogoutModal()"
            >
                Cancel
            </button>

            <button
                type="button"
                class="logout-confirm"
                onclick="confirmLogout()"
            >
                <i class='bx bx-log-out'></i>
                Logout
            </button>
        </div>

    </div>
</div>

<main class="main-content">
    <header class="page-header">
        <div>
                    <h1>Account List</h1>
                    <p>View registered accounts and their verification status.</p>
                </div>
            </header>

            <section class="accounts-card">
                <div class="filter-section">
            <div class="search-box">
                <i class="bx bx-search"></i>
                <input
                    type="search"
                    id="accountSearch"
                    name="search"
                    placeholder="Search name or email..."
                    value="<?= htmlspecialchars($search) ?>"
                >
            </div>

            <select id="roleFilter" name="role" aria-label="Filter by role">
                <option value="">All Roles</option>

                <?php foreach ($roles as $role): ?>
                    <option
                        value="<?= htmlspecialchars($role) ?>"
                        <?= $roleFilter === $role ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($role) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Email Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                No accounts found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td><?= htmlspecialchars($account['account_id_number'] ?? '—') ?></td>

                                <td>
                                    <?= htmlspecialchars($account['full_name'] ?? '') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($account['email'] ?? '') ?>
                                </td>

                                <td>
                                    <span class="role-badge">
                                        <?= htmlspecialchars($account['role'] ?? '') ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ((int) $account['email_verified'] === 1): ?>
                                        <span class="status-badge verified">Verified</span>
                                    <?php else: ?>
                                        <span class="status-badge unverified">Unverified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <p>
                Showing <?= count($accounts) ?> of <?= $totalRecords ?> account(s)
            </p>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Account list pages">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query([
                            'search' => $search,
                            'role' => $roleFilter,
                            'page' => $page - 1
                        ]) ?>" class="page-btn">
                            Previous
                        </a>
                    <?php endif; ?>

                    <span>Page <?= $page ?> of <?= $totalPages ?></span>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query([
                            'search' => $search,
                            'role' => $roleFilter,
                            'page' => $page + 1
                        ]) ?>" class="page-btn">
                            Next
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </section>
</main>

<script src="../assets/js/accounts.js"></script>
</body>
</html>