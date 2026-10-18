<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";
?>

<?php
$pending = mysqli_query($conn, "
    SELECT
        a.*,
        u.full_name,
        s.student_id_number,
        st.staff_id_number
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    WHERE a.status = 'Pending'
    ORDER BY a.appointment_date, a.appointment_time
");

$today = date("Y-m-d");

$approved_today = mysqli_query($conn, "
    SELECT
        a.*,
        u.full_name,
        s.student_id_number,
        st.staff_id_number
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    WHERE a.status = 'Approved'
    AND a.appointment_date = '$today'
    ORDER BY a.appointment_time ASC
");

$approved_upcoming = mysqli_query($conn, "
    SELECT
        a.*,
        u.full_name,
        s.student_id_number,
        st.staff_id_number
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    WHERE a.status = 'Approved'
    AND a.appointment_date > '$today'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");


// =====================================================
// COMPLETED PAGINATION + SEARCH
// =====================================================

$recordsPerPage = 15;

$completedPage = isset($_GET['completed_page'])
    ? (int) $_GET['completed_page']
    : 1;

if ($completedPage < 1) {
    $completedPage = 1;
}

$completedSearch = isset($_GET['completed_search'])
    ? trim($_GET['completed_search'])
    : '';

$completedWhere = "WHERE a.status = 'Completed'";
$completedParams = [];
$completedTypes = "";

if ($completedSearch !== '') {

    $completedWhere .= "
        AND (
            u.full_name LIKE ?
            OR s.student_id_number LIKE ?
            OR st.staff_id_number LIKE ?
            OR a.appointment_date LIKE ?
            OR a.purpose LIKE ?
            OR a.status LIKE ?
        )
    ";

    $searchParam = "%" . $completedSearch . "%";

    $completedParams[] = $searchParam;
    $completedParams[] = $searchParam;
    $completedParams[] = $searchParam;
    $completedParams[] = $searchParam;
    $completedParams[] = $searchParam;
    $completedParams[] = $searchParam;

    $completedTypes = "ssssss";
}


// COUNT COMPLETED

$completedCountSql = "
    SELECT COUNT(*) AS total
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    $completedWhere
";

$completedCountStmt = $conn->prepare($completedCountSql);

if (!$completedCountStmt) {
    die("Completed count prepare failed: " . $conn->error);
}

if (!empty($completedParams)) {
    $completedCountStmt->bind_param(
        $completedTypes,
        ...$completedParams
    );
}

$completedCountStmt->execute();

$completedCountResult = $completedCountStmt->get_result();

$totalCompleted = (int) $completedCountResult
    ->fetch_assoc()['total'];

$completedCountStmt->close();


// TOTAL COMPLETED PAGES

$totalCompletedPages = ceil(
    $totalCompleted / $recordsPerPage
);

if (
    $totalCompletedPages > 0 &&
    $completedPage > $totalCompletedPages
) {
    $completedPage = $totalCompletedPages;
}

if ($totalCompletedPages === 0) {
    $completedPage = 1;
}

$completedOffset =
    ($completedPage - 1) * $recordsPerPage;


// GET COMPLETED RECORDS

$completedSql = "
    SELECT
        a.*,
        u.full_name,
        s.student_id_number,
        st.staff_id_number
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    $completedWhere
    ORDER BY a.appointment_date DESC, a.id DESC
    LIMIT $recordsPerPage OFFSET $completedOffset
";

$completedStmt = $conn->prepare($completedSql);

if (!$completedStmt) {
    die("Completed query prepare failed: " . $conn->error);
}

if (!empty($completedParams)) {
    $completedStmt->bind_param(
        $completedTypes,
        ...$completedParams
    );
}

$completedStmt->execute();

$completed = $completedStmt->get_result();


// =====================================================
// REJECTED / CANCELLED PAGINATION + SEARCH
// =====================================================

$rejectedPage = isset($_GET['rejected_page'])
    ? (int) $_GET['rejected_page']
    : 1;

if ($rejectedPage < 1) {
    $rejectedPage = 1;
}

$rejectedSearch = isset($_GET['rejected_search'])
    ? trim($_GET['rejected_search'])
    : '';

$rejectedWhere = "
    WHERE a.status IN ('Rejected', 'Cancelled')
";

$rejectedParams = [];
$rejectedTypes = "";

if ($rejectedSearch !== '') {

    $rejectedWhere .= "
        AND (
            u.full_name LIKE ?
            OR s.student_id_number LIKE ?
            OR st.staff_id_number LIKE ?
            OR a.appointment_date LIKE ?
            OR a.reason LIKE ?
            OR a.status LIKE ?
        )
    ";

    $searchParam = "%" . $rejectedSearch . "%";

    $rejectedParams[] = $searchParam;
    $rejectedParams[] = $searchParam;
    $rejectedParams[] = $searchParam;
    $rejectedParams[] = $searchParam;
    $rejectedParams[] = $searchParam;
    $rejectedParams[] = $searchParam;

    $rejectedTypes = "ssssss";
}


// COUNT REJECTED / CANCELLED

$rejectedCountSql = "
    SELECT COUNT(*) AS total
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    $rejectedWhere
";

$rejectedCountStmt = $conn->prepare($rejectedCountSql);

if (!$rejectedCountStmt) {
    die("Rejected count prepare failed: " . $conn->error);
}

if (!empty($rejectedParams)) {
    $rejectedCountStmt->bind_param(
        $rejectedTypes,
        ...$rejectedParams
    );
}

$rejectedCountStmt->execute();

$rejectedCountResult = $rejectedCountStmt->get_result();

$totalRejected = (int) $rejectedCountResult
    ->fetch_assoc()['total'];

$rejectedCountStmt->close();


// TOTAL REJECTED PAGES

$totalRejectedPages = ceil(
    $totalRejected / $recordsPerPage
);

if (
    $totalRejectedPages > 0 &&
    $rejectedPage > $totalRejectedPages
) {
    $rejectedPage = $totalRejectedPages;
}

if ($totalRejectedPages === 0) {
    $rejectedPage = 1;
}

$rejectedOffset =
    ($rejectedPage - 1) * $recordsPerPage;


// GET REJECTED / CANCELLED RECORDS

$rejectedSql = "
    SELECT
        a.*,
        u.full_name,
        s.student_id_number,
        st.staff_id_number
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    $rejectedWhere
    ORDER BY a.appointment_date DESC, a.id DESC
    LIMIT $recordsPerPage OFFSET $rejectedOffset
";

$rejectedStmt = $conn->prepare($rejectedSql);

if (!$rejectedStmt) {
    die("Rejected query prepare failed: " . $conn->error);
}

if (!empty($rejectedParams)) {
    $rejectedStmt->bind_param(
        $rejectedTypes,
        ...$rejectedParams
    );
}

$rejectedStmt->execute();

$rejected = $rejectedStmt->get_result();
?>
    
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointments | MediTrack</title>
        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/appointments.css">
        <link rel="stylesheet" href="../assets/css/sidebar.css">
    </head>
    <body>

        <!-- SIDEBAR -->
        <?php include "../includes/sidebar.php"; ?>
            <!-- MAIN CONTENT -->
            <main class="main-content">
                <header class="content-header">
                    <div class="title-area">
                        <h1>Appointment Management</h1>
                        <p>Manage and track patient visit requests and history.</p>
                    </div>
                </header>

                <!-- TAB NAVIGATION TOGGLE -->
                <div class="tab-navigation">
                    <button class="tab-btn active" onclick="switchTab(event, 'pending')">
                        Pending <span class="tab-count count-pending"><?= mysqli_num_rows($pending) ?></span>
                    </button>
                    <button class="tab-btn" onclick="switchTab(event, 'approved')">
                        Approved <span class="tab-count count-approved">
                        <?= mysqli_num_rows($approved_today) + mysqli_num_rows($approved_upcoming) ?>
                        </span>
                    </button>
                    <button class="tab-btn" onclick="switchTab(event, 'completed')">
                        Completed
                    </button>
                    <button class="tab-btn" onclick="switchTab(event, 'rejected')">
                        Rejected / Cancelled
                    </button>
                </div>

                <!-- MAIN DATA CARD -->
                <div class="appointment-card">
                    
                    <!-- 1. PENDING SECTION -->
                    <div id="pending" class="tab-content active">
                        <div class="table-header">
                            <h2>Pending Requests</h2>
                        </div>
                        <table class="appointment-table">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Full Name</th>
                                    <th>Requested Date</th>
                                    <th>Time</th>
                                    <th>Purpose</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if(mysqli_num_rows($pending) > 0): ?>

                                <?php while($row = mysqli_fetch_assoc($pending)): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= !empty($row['student_id_number'])
                                            ? $row['student_id_number']
                                            : $row['staff_id_number']; ?>
                                        </strong>
                                    </td>

        

                                    <td><?= htmlspecialchars($row['full_name']); ?></td>

                                    <td><?= date("M d, Y", strtotime($row['appointment_date'])); ?></td>

                                    <td><?= date("h:i A", strtotime($row['appointment_time'])); ?></td>

                                    <td><?= htmlspecialchars($row['purpose']); ?></td>

                                    <td>

                                        <form action="../actions/approve_appointment.php" method="POST" style="display:inline;">

                                            <input
                                                type="hidden"
                                                name="appointment_id"
                                                value="<?= $row['id']; ?>">

                                            <button
                                                type="submit"
                                                class="btn-icon icon-approve"
                                                title="Approve">

                                                <i class="fas fa-check"></i>

                                            </button>

                                        </form>

                                        <button
                                            class="btn-icon icon-reject"
                                            title="Reject"
                                            onclick="openRejectModal('<?= htmlspecialchars($row['full_name']); ?>', <?= $row['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>

                                    </td>

                                </tr>

                                <?php endwhile; ?>

                                <?php else: ?>

                                <tr>
                                    <td colspan="6" style="text-align:center;">
                                        No pending appointments found.
                                    </td>
                                </tr>

                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>

                    <!-- 2. APPROVED SECTION -->
                    <div id="approved" class="tab-content">
                        <div class="table-header">
                            <h2>Approved Appointments</h2>
                        </div>
                        <table class="appointment-table">

                        <h3 class="appointment-group-title">
                            <i class="fas fa-calendar-day"></i>
                            Today
                        </h3>
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Full Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Manage</th>
                                </tr>
                            </thead>
                            <tbody>


                                <?php if(mysqli_num_rows($approved_today) > 0): ?>

                                <?php while($row = mysqli_fetch_assoc($approved_today)): ?>

                                <tr>

                                <td>
                                    <strong>
                                    <?= !empty($row['student_id_number'])
                                    ? $row['student_id_number']
                                    : $row['staff_id_number']; ?>
                                    </strong>
                                </td>


                                <td><?= htmlspecialchars($row['full_name']); ?></td>


                                <td><?= date("M d, Y", strtotime($row['appointment_date'])); ?></td>


                                <td><?= date("h:i A", strtotime($row['appointment_time'])); ?></td>


                                <td>
                                    <span class="status-pill stat-approved">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>


                                <td>
 
                                    
                                    <form
                                        action="../actions/complete_appointment.php"
                                        method="POST"
                                        style="display:inline;">

                                        <button
                                            type="button"
                                            class="btn-icon btn-complete"
                                            title="Mark Completed"
                                            onclick='openCompleteModal(
                                                <?= htmlspecialchars(
                                                    json_encode($row["full_name"]),
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ); ?>,
                                                <?= (int) $row["id"]; ?>
                                            )'>

                                            <i class="fas fa-check"></i>

                                        </button>
                                    </form>


                                    <button class="btn-icon btn-resched"
                                        title="Reschedule"
                                        onclick="openRescheduleModal('<?= htmlspecialchars($row['full_name']); ?>', <?= $row['id']; ?>)">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>


                                    <button class="btn-icon btn-cancel"
                                        title="Cancel"
                                        onclick="openCancelModal('<?= htmlspecialchars($row['full_name']); ?>', <?= $row['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>

                                </td>


                                </tr>

                                <?php endwhile; ?>


                                <?php else: ?>

                            <tr>
                                <td colspan="6" style="text-align:center;">
                                No appointments today.
                                </td>
                            </tr>

                                <?php endif; ?>





                    <!-- UPCOMING HEADER -->

                    <tr>
                        <td colspan="6" class="appointment-group-title upcoming">
                        <i class="fas fa-calendar-alt"></i>
                            Upcoming Appointments
                        </td>
                    </tr>

                    <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Full Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Manage</th>
                    </tr>
                    </thead>



                    <?php if(mysqli_num_rows($approved_upcoming) > 0): ?>

                    <?php while($row = mysqli_fetch_assoc($approved_upcoming)): ?>


                    <tr>

                        <td>
                            <strong>
                            <?= !empty($row['student_id_number'])
                            ? $row['student_id_number']
                            : $row['staff_id_number']; ?>
                            </strong>
                        </td>


                        <td><?= htmlspecialchars($row['full_name']); ?></td>


                        <td><?= date("M d, Y", strtotime($row['appointment_date'])); ?></td>


                        <td><?= date("h:i A", strtotime($row['appointment_time'])); ?></td>


                        <td>
                            <span class="status-pill stat-approved">
                            <?= $row['status']; ?>
                            </span>
                        </td>


                        <td>

                        
                            <form
                                action="../actions/complete_appointment.php"
                                method="POST"
                                style="display:inline;">

                                <button
                                    type="button"
                                    class="btn-icon btn-complete"
                                    title="Mark Completed"
                                    onclick='openCompleteModal(
                                        <?= htmlspecialchars(
                                            json_encode($row["full_name"]),
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>,
                                        <?= (int) $row["id"]; ?>
                                    )'>

                                    <i class="fas fa-check"></i>

                                </button>
                            </form>



                            <button class="btn-icon btn-resched"
                                title="Reschedule"
                                onclick="openRescheduleModal('<?= htmlspecialchars($row['full_name']); ?>', <?= $row['id']; ?>)">
                                <i class="fas fa-calendar-alt"></i>
                            </button>


                            <button class="btn-icon btn-cancel"
                                title="Cancel"
                                onclick="openCancelModal('<?= htmlspecialchars($row['full_name']); ?>', <?= $row['id']; ?>)">
                                <i class="fas fa-times"></i>
                            </button>

                        </td>


                    </tr>


                    <?php endwhile; ?>


                    <?php else: ?>

                    <tr>
                        <td colspan="6" style="text-align:center;">
                        No upcoming appointments.
                        </td>
                    </tr>

                    <?php endif; ?>



                            </tbody>
                        </table>
                    </div>

                    <!-- 3. COMPLETED SECTION -->
                    <div id="completed" class="tab-content">
                        <div class="table-header">
                            <h2>Completed History</h2>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input
                                    type="text"
                                    id="completedSearch"
                                    class="table-search"
                                    value="<?= htmlspecialchars($completedSearch); ?>"
                                    placeholder="Search history..."
                                >
                            </div>
                        </div>
                        <table class="appointment-table read-only">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Full Name</th>
                                    <th>Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if(mysqli_num_rows($completed) > 0): ?>

                                <?php while($row = mysqli_fetch_assoc($completed)): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= !empty($row['student_id_number'])
                                            ? $row['student_id_number']
                                            : $row['staff_id_number']; ?>
                                        </strong>
                                    </td>

                                    <td><?= htmlspecialchars($row['full_name']); ?></td>

                                    <td><?= date("M d, Y", strtotime($row['appointment_date'])); ?></td>

                                    <td><?= htmlspecialchars($row['purpose']); ?></td>

                                    <td>
                                        <span class="status-pill stat-completed">
                                        <?= htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>

                                </tr>

                                <?php endwhile; ?>

                                <?php else: ?>

                                <tr>
                                    <td colspan="5" style="text-align:center;">
                                        No completed appointments found.
                                    </td>
                                </tr>

                                <?php endif; ?>

                            </tbody>
                        </table>
                        <div class="table-footer">

                                <p>
                                    Showing
                                    <?= $totalCompleted > 0
                                        ? (($completedPage - 1) * $recordsPerPage + 1)
                                        : 0
                                    ?>
                                    -
                                    <?= min(
                                        $completedPage * $recordsPerPage,
                                        $totalCompleted
                                    ) ?>
                                    of
                                    <?= $totalCompleted ?>
                                    <?= $totalCompleted === 1 ? 'record' : 'records' ?>
                                </p>

                                <?php if ($totalCompletedPages > 1): ?>

                                <div class="pagination">

                                    <?php if ($completedPage > 1): ?>

                                        <a
                                            href="?completed_page=<?= $completedPage - 1 ?>&completed_search=<?= urlencode($completedSearch) ?>#completed"
                                            class="btn-page"
                                        >
                                            <i class="bx bx-chevron-left"></i>
                                        </a>

                                    <?php else: ?>

                                        <button class="btn-page" disabled>
                                            <i class="bx bx-chevron-left"></i>
                                        </button>

                                    <?php endif; ?>


                                    <?php for (
                                        $page = 1;
                                        $page <= $totalCompletedPages;
                                        $page++
                                    ): ?>

                                        <a
                                            href="?completed_page=<?= $page ?>&completed_search=<?= urlencode($completedSearch) ?>#completed"
                                            class="btn-page <?= $page == $completedPage ? 'active' : '' ?>"
                                        >
                                            <?= $page ?>
                                        </a>

                                    <?php endfor; ?>


                                    <?php if ($completedPage < $totalCompletedPages): ?>

                                        <a
                                            href="?completed_page=<?= $completedPage + 1 ?>&completed_search=<?= urlencode($completedSearch) ?>#completed"
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
                    </div>

                    <!-- 4. REJECTED SECTION -->
                    <div id="rejected" class="tab-content">
                        <div class="table-header">
                            <h2>Rejected / Cancelled</h2>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input
                                    type="text"
                                    id="rejectedSearch"
                                    class="table-search"
                                    value="<?= htmlspecialchars($rejectedSearch); ?>"
                                    placeholder="Search rejected..."
                                >
                            </div>
                        </div>
                        <table class="appointment-table read-only">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Full Name</th>
                                    <th>Original Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if(mysqli_num_rows($rejected) > 0): ?>

                                <?php while($row = mysqli_fetch_assoc($rejected)): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= !empty($row['student_id_number'])
                                            ? $row['student_id_number']
                                            : $row['staff_id_number']; ?>
                                        </strong>
                                    </td>

                                    <td><?= htmlspecialchars($row['full_name']); ?></td>

                                    <td><?= date("M d, Y", strtotime($row['appointment_date'])); ?></td>

                                    <td><?= htmlspecialchars($row['reason']); ?></td>

                                    <td>
                                        <span class="status-pill stat-rejected">
                                        <?= htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>

                                </tr>

                                <?php endwhile; ?>

                                <?php else: ?>

                                <tr>
                                    <td colspan="5" style="text-align:center;">
                                        No rejected appointments found.
                                    </td>
                                </tr>

                                <?php endif; ?>

                            </tbody>
                        </table>
                        <div class="table-footer">

                                <p>
                                    Showing
                                    <?= $totalRejected > 0
                                        ? (($rejectedPage - 1) * $recordsPerPage + 1)
                                        : 0
                                    ?>
                                    -
                                    <?= min(
                                        $rejectedPage * $recordsPerPage,
                                        $totalRejected
                                    ) ?>
                                    of
                                    <?= $totalRejected ?>
                                    <?= $totalRejected === 1 ? 'record' : 'records' ?>
                                </p>

                                <?php if ($totalRejectedPages > 1): ?>

                                <div class="pagination">

                                    <?php if ($rejectedPage > 1): ?>

                                        <a
                                            href="?rejected_page=<?= $rejectedPage - 1 ?>&rejected_search=<?= urlencode($rejectedSearch) ?>#rejected"
                                            class="btn-page"
                                        >
                                            <i class="bx bx-chevron-left"></i>
                                        </a>

                                    <?php else: ?>

                                        <button class="btn-page" disabled>
                                            <i class="bx bx-chevron-left"></i>
                                        </button>

                                    <?php endif; ?>


                                    <?php for (
                                        $page = 1;
                                        $page <= $totalRejectedPages;
                                        $page++
                                    ): ?>

                                        <a
                                            href="?rejected_page=<?= $page ?>&rejected_search=<?= urlencode($rejectedSearch) ?>#rejected"
                                            class="btn-page <?= $page == $rejectedPage ? 'active' : '' ?>"
                                        >
                                            <?= $page ?>
                                        </a>

                                    <?php endfor; ?>


                                    <?php if ($rejectedPage < $totalRejectedPages): ?>

                                        <a
                                            href="?rejected_page=<?= $rejectedPage + 1 ?>&rejected_search=<?= urlencode($rejectedSearch) ?>#rejected"
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
                    </div>

                </div>
            </main>


        <!-- REJECT MODAL -->
    <div id="rejectModal" class="modal-overlay">
        <div class="modal-box">

            <div class="modal-header">
                <h2>Reject Appointment</h2>
                <button class="close-btn" onclick="closeRejectModal()">×</button>
            </div>

            <form id="rejectForm" action="../actions/reject_appointment.php" method="POST">
                <input type="hidden" name="appointment_id" id="rejAppointmentId">

                <div class="form-group">
                    <label>Patient Name</label>
                    <input type="text" id="rejPatientName" readonly>
                </div>

                <div class="form-group">
                    <label>Reason for Rejection</label>
                    <select id="rejReason" name="reason" required>
                        <option value="">Select reason</option>
                        <option>Schedule conflict</option>
                        <option>Physician unavailable</option>
                        <option>Emergency case priority</option>
                        <option>Invalid request</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Additional Notes (optional)</label>
                    <textarea id="rejNotes" name="note" rows="3" placeholder="Add extra details..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn-danger">Confirm Reject</button>
                </div>

            </form>
        </div>
    </div>

        <!-- RESCHEDULE MODAL -->
    <div id="rescheduleModal" class="modal-overlay">
        <div class="modal-box">

            <div class="modal-header">
                <h2>Reschedule Appointment</h2>
                <button class="close-btn" onclick="closeRescheduleModal()">×</button>
            </div>

            <form id="rescheduleForm" action="../actions/reschedule_appointment.php" method="POST">
                <input type="hidden" name="appointment_id" id="resAppointmentId">

                <div class="form-group">
                    <label>Patient Name</label>
                    <input type="text" id="resPatientName" readonly>
                </div>

                <div class="form-group">
                    <label>New Date</label>
                    <input type="date" id="resDate" name="appointment_date" required>
                </div>

                <div class="form-group">
                    <label>New Time</label>
                    <select id="resTime" name="appointment_time" required>
                        <option value="">Select Time</option>
                        <option>08:30 AM</option>
                        <option>10:00 AM</option>
                        <option>01:30 PM</option>
                        <option>03:00 PM</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeRescheduleModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>

            </form>
        </div>
    </div>

    <!-- CANCEL MODAL -->
    <div id="cancelModal" class="modal-overlay">
        <div class="modal-box">

            <div class="modal-header">
                <h2>Cancel Appointment</h2>
                <button class="close-btn" onclick="closeCancelModal()">×</button>
            </div>

            <form id="cancelForm" action="../actions/cancel_appointment.php" method="POST">
                <input type="hidden" name="appointment_id" id="canAppointmentId">

                <div class="form-group">
                    <label>Patient Name</label>
                    <input type="text" id="canPatientName" readonly>
                </div>

                <div class="form-group">
                    <label>Reason for Cancellation</label>
                    <select id="canReason" name="reason" required>
                        <option value="">Select reason</option>
                        <option>Patient not available</option>
                        <option>Schedule conflict</option>
                        <option>Emergency case</option>
                        <option>System adjustment</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Additional Notes (optional)</label>
                    <textarea id="canNotes" name="note" rows="3" placeholder="Add extra details..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeCancelModal()">Back</button>
                    <button type="submit" class="btn-danger">Confirm Cancel</button>
                </div>

            </form>
        </div>
    </div>
    
    
    <!-- COMPLETE APPOINTMENT MODAL -->
    <div id="completeModal" class="modal-overlay">
        <div class="modal-box">

            <div class="modal-header">
                <h2>Complete Appointment</h2>
                <button
                    type="button"
                    class="close-btn"
                    onclick="closeCompleteModal()">
                    ×
                </button>
            </div>

            <form
                id="completeForm"
                action="../actions/complete_appointment.php"
                method="POST">

                <input
                    type="hidden"
                    name="appointment_id"
                    id="completeAppointmentId">

                <div class="form-group">
                    <label>Patient Name</label>
                    <input
                        type="text"
                        id="completePatientName"
                        readonly>
                </div>

                <p class="complete-confirmation-text">Are you sure you want to mark this appointment as completed?</p>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="btn-cancel"
                        onclick="closeCompleteModal()">
                        Cancel
                    </button>

                    <button type="submit" class="btn-primary">
                        Confirm Completed
                    </button>
                </div>

            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>

<div id="messageModal" class="modal active">

    <div class="modal-content message-modal">

        <div class="message-icon success">

            <?php if(isset($_SESSION['success'])): ?>

                <i class='bx bx-check-circle'></i>

            <?php else: ?>

                <i class='bx bx-x-circle'></i>

            <?php endif; ?>

        </div>


        <h2>
            <?= isset($_SESSION['success'])
                ? $_SESSION['success']
                : "Error"; ?>
        </h2>

        <p>
            <?= isset($_SESSION['success_message'])
                ? $_SESSION['success_message']
                : (isset($_SESSION['success'])
                    ? $_SESSION['success']
                    : $_SESSION['error']); ?>
        </p>


        <div class="modal-actions">

            <button 
                class="btn-primary"
                onclick="closeMessageModal()">

                OK

            </button>

        </div>


    </div>

</div>


<?php
unset($_SESSION['success']);
unset($_SESSION['error']);
endif;
?>
        <script src="../assets/js/appointments.js"></script>
        <script src="../includes/includes.js"></script>
    </body>
    </html>