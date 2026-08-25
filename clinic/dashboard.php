<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";

// ==========================
// DASHBOARD STATISTICS
// ==========================

// TOTAL CONSULTATIONS
$consultationSql = "
    SELECT COUNT(*) AS total
    FROM consultations
";

$consultationResult = mysqli_query($conn, $consultationSql);
$totalConsultations = mysqli_fetch_assoc($consultationResult)['total'];


// PENDING APPOINTMENTS
$pendingSql = "
    SELECT COUNT(*) AS total
    FROM appointments
    WHERE status = 'Pending'
";

$pendingResult = mysqli_query($conn, $pendingSql);
$pendingAppointments = mysqli_fetch_assoc($pendingResult)['total'];


// CONSULTATIONS TODAY
$todaySql = "
    SELECT COUNT(*) AS total
    FROM consultations
    WHERE date = CURDATE()
";

$todayResult = mysqli_query($conn, $todaySql);
$consultationsToday = mysqli_fetch_assoc($todayResult)['total'];


// Medicine Alerts
$medicine_alert_sql = "
    SELECT COUNT(*) AS alert_count
    FROM medicines
    WHERE status = 'Active'
    AND is_quarantined = 0
    AND (
        quantity <= low_stock_threshold
        OR expiry_date < CURDATE()
        OR expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    )
";

$medicine_alert_result = mysqli_query($conn, $medicine_alert_sql);
$medicine_alert_data = mysqli_fetch_assoc($medicine_alert_result);
$medicine_alert_count = $medicine_alert_data['alert_count'];

// ==========================
// ANNUAL TOP 5 COMPLAINTS
// ==========================

$topComplaintsSql = "
    SELECT complaint, COUNT(*) AS total
    FROM consultations
    WHERE YEAR(date) = YEAR(CURDATE())
    AND complaint IS NOT NULL
    AND complaint != ''
    GROUP BY complaint
    ORDER BY total DESC
    LIMIT 5
";

$topComplaintsResult = mysqli_query($conn, $topComplaintsSql);

$complaintLabels = [];
$complaintCounts = [];

while ($row = mysqli_fetch_assoc($topComplaintsResult)) {
    $complaintLabels[] = $row['complaint'];
    $complaintCounts[] = $row['total'];
}

// ==========================
// ANNUAL CONSULTATION TRENDS
// ==========================

$consultationTrendSql = "
    SELECT MONTH(date) AS month, COUNT(*) AS total
    FROM consultations
    WHERE YEAR(date) = YEAR(CURDATE())
    GROUP BY MONTH(date)
    ORDER BY month
";

$consultationTrendResult = mysqli_query($conn, $consultationTrendSql);

$consultationMonthlyCounts = array_fill(0, 12, 0);

while ($row = mysqli_fetch_assoc($consultationTrendResult)) {
    $monthIndex = (int)$row['month'] - 1;
    $consultationMonthlyCounts[$monthIndex] = (int)$row['total'];
}


// ==========================
// CONSULTATION DEPARTMENT DISTRIBUTION
// ==========================

$departmentSql = "
    SELECT department, COUNT(*) AS total
    FROM consultations
    WHERE YEAR(date) = YEAR(CURDATE())
    AND department IS NOT NULL
    AND department != ''
    GROUP BY department
    ORDER BY total DESC
";

$departmentResult = mysqli_query($conn, $departmentSql);

$departmentLabels = [];
$departmentCounts = [];

while ($row = mysqli_fetch_assoc($departmentResult)) {
    $departmentLabels[] = $row['department'];
    $departmentCounts[] = (int)$row['total'];
}

// ==========================
// ANNUAL APPOINTMENT TRENDS
// ==========================

$appointmentTrendSql = "
    SELECT MONTH(appointment_date) AS month, COUNT(*) AS total
    FROM appointments
    WHERE YEAR(appointment_date) = YEAR(CURDATE())
    AND status = 'Completed'
    GROUP BY MONTH(appointment_date)
    ORDER BY month
";

$appointmentTrendResult = mysqli_query($conn, $appointmentTrendSql);

$appointmentMonthlyCounts = array_fill(0, 12, 0);

while ($row = mysqli_fetch_assoc($appointmentTrendResult)) {
    $monthIndex = (int)$row['month'] - 1;
    $appointmentMonthlyCounts[$monthIndex] = (int)$row['total'];
}


// ==========================
// APPOINTMENT DEPARTMENT DISTRIBUTION
// ==========================

$appointmentDeptSql = "
    SELECT 
        COALESCE(s.department, st.department) AS department,
        COUNT(*) AS total
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    WHERE YEAR(a.appointment_date) = YEAR(CURDATE())
    AND a.status = 'Completed'
    AND COALESCE(s.department, st.department) IS NOT NULL
    AND COALESCE(s.department, st.department) != ''
    GROUP BY COALESCE(s.department, st.department)
    ORDER BY total DESC
";

$appointmentDeptResult = mysqli_query($conn, $appointmentDeptSql);

$appointmentDeptLabels = [];
$appointmentDeptCounts = [];

while ($row = mysqli_fetch_assoc($appointmentDeptResult)) {
    $appointmentDeptLabels[] = $row['department'];
    $appointmentDeptCounts[] = (int)$row['total'];
}

// ==========================
// RECENT ACTIVITY
// ==========================

$recentActivitySql = "
    SELECT 
        activity_type,
        action,
        description,
        created_at
    FROM activity_logs
    ORDER BY created_at DESC
    LIMIT 5
";

$recentActivityResult = mysqli_query($conn, $recentActivitySql);

$recentActivities = [];

while ($row = mysqli_fetch_assoc($recentActivityResult)) {
    $recentActivities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediTrack | Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<!-- SIDEBAR -->
<?php include "../includes/sidebar.php"; ?>

<main class="main-content">

<div class="content-body">

    <!-- HEADER -->
    <div class="header-titles">
        <h1>Clinic Overview</h1>
        <p>Annual health analytics and system overview.</p>

        <p>Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong>!</p>
    </div>

    <!-- KPI -->
    <div class="stats-row">

        <div class="stat-card">
            <div class="stat-icon bg-light-blue">
                <i class='bx bxs-user-plus'></i>
            </div>
            <div class="stat-data">
                <span class="label">Total Consultations</span>
                <span class="value"><?= $totalConsultations ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-light-teal">
                <i class='bx bxs-calendar-exclamation'></i>
            </div>
            <div class="stat-data">
                <span class="label">Pending Appointments</span>
                <span class="value"><?= $pendingAppointments ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-light-orange">
                <i class='bx bxs-user-detail'></i>
            </div>
            <div class="stat-data">
                <span class="label">Consultations Today</span>
                <span class="value"><?= $consultationsToday ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-light-purple">
                <i class='bx bxs-capsule'></i>
            </div>
            <div class="stat-data">
                <span class="label">Medicine Alerts</span>
                <span class="value"><?= $medicine_alert_count ?></span>
            </div>
        </div>

    </div>

    <!-- TOP ROW -->
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">

        <!-- RECENT ACTIVITY -->
        <div class="table-container">

    <div class="table-header">
        <h3>Recent Activity</h3>
        <a href="#" class="btn-link">View All</a>
    </div>


    <div class="activity-list">
        <?php foreach ($recentActivities as $activity): ?>

            <?php
                if ($activity['activity_type'] === 'Appointment') {

                    $iconClass = 'approved';
                    $icon = 'bx-calendar';

                    if ($activity['action'] === 'Approved') {
                        $iconClass = 'approved';
                        $icon = 'bx-check-circle';
                    }
                    elseif ($activity['action'] === 'Completed') {
                        $iconClass = 'approved';
                        $icon = 'bx-check-circle';
                    }
                    elseif ($activity['action'] === 'Cancelled') {
                        $iconClass = 'cancel';
                        $icon = 'bx-x-circle';
                    }
                    elseif ($activity['action'] === 'Rejected') {
                        $iconClass = 'cancel';
                        $icon = 'bx-x-circle';
                    }
                    elseif ($activity['action'] === 'Rescheduled') {
                        $iconClass = 'pending';
                        $icon = 'bx-calendar';
                    }
                    elseif ($activity['action'] === 'Created') {
                        $iconClass = 'pending';
                        $icon = 'bx-calendar-plus';
                    }

                }
                elseif ($activity['activity_type'] === 'Medicine') {

                    $iconClass = 'medicine';
                    $icon = 'bx-capsule';

                }
                else {

                    $iconClass = 'pending';
                    $icon = 'bx-info-circle';

                }
            ?>

            <div class="activity-item">

                <div class="activity-icon <?= $iconClass ?>">
                    <i class='bx <?= $icon ?>'></i>
                </div>

                <div class="activity-details">

                    <h4>
                        <?= htmlspecialchars($activity['action']) ?>
                        <?= htmlspecialchars($activity['activity_type']) ?>
                    </h4>

                    <p>
                        <?= htmlspecialchars($activity['description']) ?>
                    </p>

                    <span>
                        <?= date("M d, Y • h:i A", strtotime($activity['created_at'])) ?>
                    </span>

                </div>

            </div>

        <?php endforeach; ?>


    </div>

</div>

        <!-- TOP 5 COMPLAINTS -->
        <div class="table-container">
            <div class="table-header">
                <h3>Top 5 Complaints</h3>
            </div>

            <canvas id="complaintsChart"></canvas>
        </div>

    </div>

    <!-- MIDDLE ROW -->
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-top:20px;">

        <div class="table-container">
            <div class="table-header">
                <h3>Annual Consultation Trends</h3>
            </div>
            <canvas id="consultationChart"></canvas>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>Consultation Department Distribution</h3>
            </div>
            <canvas id="departmentChart"></canvas>
        </div>

    </div>

    <!-- BOTTOM ROW -->
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-top:20px;">

        <div class="table-container">
            <div class="table-header">
                <h3>Annual Appointment Trends</h3>
            </div>
            <canvas id="appointmentChart"></canvas>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>Appointment Department Distribution</h3>
            </div>
            <canvas id="appointmentDeptChart"></canvas>
        </div>

    </div>

</div>

</main>

<!-- CANCEL MODAL -->
<div id="cancelModal" class="modal-overlay">
    <div class="modal-box">

        <div class="modal-header">
            <h2>Cancel Appointment</h2>
            <button class="close-btn" onclick="closeCancelModal()">×</button>
        </div>

        <form id="cancelForm">

            <div class="form-group">
                <label>Patient Name</label>
                <input type="text" id="canPatientName" readonly>
            </div>

            <div class="form-group">
                <label>Reason for Cancellation</label>
                <select id="canReason" required>
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
                <textarea id="rejNotes" rows="3" placeholder="Add extra details..."></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeCancelModal()">Back</button>
                <button type="submit" class="btn-danger">Confirm Cancel</button>
            </div>

        </form>

    </div>
</div>

<script>
    const complaintLabels = <?= json_encode($complaintLabels) ?>;
    const complaintCounts = <?= json_encode($complaintCounts) ?>;

    const consultationMonthlyCounts = <?= json_encode($consultationMonthlyCounts) ?>;

    const departmentLabels = <?= json_encode($departmentLabels) ?>;
    const departmentCounts = <?= json_encode($departmentCounts) ?>;

    const appointmentMonthlyCounts = <?= json_encode($appointmentMonthlyCounts) ?>;

    const appointmentDeptLabels = <?= json_encode($appointmentDeptLabels) ?>;
    const appointmentDeptCounts = <?= json_encode($appointmentDeptCounts) ?>;
</script>

<script src="../includes/includes.js"></script>
<script src="../assets/js/dashboard.js"></script>

</body>
</html>