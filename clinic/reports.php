<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// ==========================
// MONTH & YEAR FILTER
// ==========================

$currentMonth = (int) date('n');
$currentYear = (int) date('Y');

$selectedMonth = isset($_GET['month'])
    ? (int) $_GET['month']
    : $currentMonth;

$selectedYear = isset($_GET['year'])
    ? (int) $_GET['year']
    : $currentYear;

if ($selectedMonth < 1 || $selectedMonth > 12) {
    $selectedMonth = date('n');
}

if ($selectedYear < 2020 || $selectedYear > 2100) {
    $selectedYear = date('Y');
}

$monthName = date(
    'F',
    mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)
);

$startDate = sprintf(
    '%04d-%02d-01',
    $selectedYear,
    $selectedMonth
);

$endDate = date(
    'Y-m-t',
    strtotime($startDate)
);

// ==========================
// PREVIOUS MONTH
// ==========================

$previousMonthDate = strtotime($startDate . ' -1 month');

$previousMonth = (int) date('n', $previousMonthDate);
$previousYear = (int) date('Y', $previousMonthDate);

$previousStartDate = date(
    'Y-m-01',
    $previousMonthDate
);

$previousEndDate = date(
    'Y-m-t',
    $previousMonthDate
);

// ==========================
// TOTAL CONSULTATIONS
// ==========================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM consultations
    WHERE date BETWEEN ? AND ?
");

$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();

$totalConsultations = $stmt->get_result()
    ->fetch_assoc()['total'];

// ==========================
// PREVIOUS MONTH CONSULTATIONS
// ==========================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM consultations
    WHERE date BETWEEN ? AND ?
");

$stmt->bind_param("ss", $previousStartDate, $previousEndDate);
$stmt->execute();

$previousConsultations = (int) $stmt->get_result()
    ->fetch_assoc()['total'];

if ($previousConsultations > 0) {
    $consultationChange =
        (($totalConsultations - $previousConsultations) / $previousConsultations) * 100;
} else {
    $consultationChange = $totalConsultations > 0 ? 100 : 0;
}
// ==========================
// PATIENT TYPE COUNTS
// ==========================

$stmt = $conn->prepare("
    SELECT
        patient_type,
        COUNT(*) AS total
    FROM consultations
    WHERE date BETWEEN ? AND ?
    GROUP BY patient_type
");

$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();

$typeResult = $stmt->get_result();

$studentVolume = 0;
$teachingStaffVolume = 0;
$nonTeachingStaffVolume = 0;

while ($row = $typeResult->fetch_assoc()) {

    if ($row['patient_type'] === 'student') {
        $studentVolume = (int) $row['total'];
    }

    if ($row['patient_type'] === 'tstaff') {
        $teachingStaffVolume = (int) $row['total'];
    }

    if ($row['patient_type'] === 'nstaff') {
        $nonTeachingStaffVolume = (int) $row['total'];
    }
}

$staffVolume =
    $teachingStaffVolume +
    $nonTeachingStaffVolume;

// ==========================
// PATIENT TYPE PERCENTAGES
// ==========================

if ($totalConsultations > 0) {
    $studentPercentage =
        ($studentVolume / $totalConsultations) * 100;

    $staffPercentage =
        ($staffVolume / $totalConsultations) * 100;
} else {
    $studentPercentage = 0;
    $staffPercentage = 0;
}

// ==========================
// TOTAL MEDICINES DISPENSED
// ==========================

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(cm.quantity_used), 0) AS total
    FROM consultation_medicine cm
    INNER JOIN consultations c
        ON cm.consultation_id = c.id
    WHERE c.date BETWEEN ? AND ?
");

$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();

$totalMedicines = (int) $stmt->get_result()
    ->fetch_assoc()['total'];

// ==========================
// PREVIOUS MONTH MEDICINES
// ==========================

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(cm.quantity_used), 0) AS total
    FROM consultation_medicine cm
    INNER JOIN consultations c
        ON cm.consultation_id = c.id
    WHERE c.date BETWEEN ? AND ?
");

$stmt->bind_param("ss", $previousStartDate, $previousEndDate);
$stmt->execute();

$previousMedicines = (int) $stmt->get_result()
    ->fetch_assoc()['total'];

if ($previousMedicines > 0) {
    $medicineChange =
        (($totalMedicines - $previousMedicines) / $previousMedicines) * 100;
} else {
    $medicineChange = $totalMedicines > 0 ? 100 : 0;
}

// ==========================
// DAILY CONSULTATION VOLUME
// ==========================

$stmt = $conn->prepare("
    SELECT
        DAY(date) AS day,
        COUNT(*) AS total
    FROM consultations
    WHERE date BETWEEN ? AND ?
    GROUP BY DAY(date)
    ORDER BY day ASC
");

$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();

$dailyResult = $stmt->get_result();

$dailyLabels = [];
$dailyData = [];

while ($row = $dailyResult->fetch_assoc()) {

    $dailyLabels[] = $row['day'];
    $dailyData[] = (int) $row['total'];
}

// ==========================
// TOP 5 COMPLAINTS
// ==========================

$stmt = $conn->prepare("
    SELECT
        complaint,
        COUNT(*) AS total
    FROM consultations
    WHERE date BETWEEN ? AND ?
      AND complaint IS NOT NULL
      AND complaint != ''
    GROUP BY complaint
    ORDER BY total DESC
    LIMIT 5
");

$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();

$complaintResult = $stmt->get_result();

$topComplaints = [];

while ($row = $complaintResult->fetch_assoc()) {

    $topComplaints[] = [
        'complaint' => $row['complaint'],
        'total' => (int) $row['total']
    ];
}

// ==========================
// TOP 5 MEDICINES
// ==========================

$stmt = $conn->prepare("
    SELECT
        m.medicine_name,
        SUM(cm.quantity_used) AS total
    FROM consultation_medicine cm
    INNER JOIN consultations c
        ON cm.consultation_id = c.id
    INNER JOIN medicines m
        ON cm.medicine_id = m.id
    WHERE c.date BETWEEN ? AND ?
    GROUP BY m.medicine_name
    ORDER BY total DESC
    LIMIT 5
");

$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();

$medicineResult = $stmt->get_result();

$topMedicines = [];

while ($row = $medicineResult->fetch_assoc()) {

    $topMedicines[] = [
        'medicine_name' => $row['medicine_name'],
        'total' => (int) $row['total']
    ];
}

// ==========================
// CONSULTATION SUMMARY
// ==========================

$stmt = $conn->prepare("
    SELECT
        c.id,
        c.date,
        c.full_name,
        c.patient_type,
        c.complaint,

        COALESCE(
            GROUP_CONCAT(
                CONCAT(m.medicine_name, ' (', cm.quantity_used, ')')
                SEPARATOR ', '
            ),
            'None'
        ) AS medicines

    FROM consultations c

    LEFT JOIN consultation_medicine cm
        ON c.id = cm.consultation_id

    LEFT JOIN medicines m
        ON cm.medicine_id = m.id

    WHERE c.date BETWEEN ? AND ?

    GROUP BY c.id

    ORDER BY c.date DESC, c.time DESC

    LIMIT 5
");

$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();

$summaryResult = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report | MediTrack</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <!-- Icons & Charts -->
     <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/reports.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">

</head>
<body>

    <!-- SIDEBAR INCLUDE -->
    <?php include "../includes/sidebar.php"; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="bg-gradient"></div>

            <header class="content-header">
                <div class="title-area">
                    <h1>Monthly Consultation Report</h1>
                    <p>Detailed performance and health trends for the selected month.</p>
                </div>
                
                <!-- MONTH SELECTOR -->
                <div class="report-controls">
                    <div class="selector-group">
                        <i class="fas fa-calendar-alt"></i>
                        <select id="monthSelect" name="month" onchange="changeReport()">

                            <?php for ($month = 1; $month <= 12; $month++): ?>

                                <option
                                    value="<?= $month ?>"
                                    <?= $month === $selectedMonth ? 'selected' : '' ?>
                                >
                                    <?= date('F', mktime(0, 0, 0, $month, 1)) ?>
                                </option>

                            <?php endfor; ?>
                        </select>

                        <select id="yearSelect" name="year" onchange="changeReport()">
                            <?php for ($year = date('Y'); $year >= 2024; $year--): ?>

                                    <option
                                        value="<?= $year ?>"
                                        <?= $year === $selectedYear ? 'selected' : '' ?>
                                    >       
                                        <?= $year ?>
                                    </option>

                                <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </header>

            <!-- STATS SUMMARY -->
            <section class="stats-row">

                <!-- TOTAL CONSULTATIONS -->
                <div class="mini-card">
                    <label>Total Consultations</label>

                    <div class="card-val">
                        <h3><?= $totalConsultations ?></h3>

                        <?php if ($consultationChange > 0): ?>
                            <span class="comparison positive">
                                ↑ <?= number_format(abs($consultationChange), 1) ?>%
                            </span>
                        <?php elseif ($consultationChange < 0): ?>
                            <span class="comparison negative">
                                ↓ <?= number_format(abs($consultationChange), 1) ?>%
                            </span>
                        <?php else: ?>
                            <span class="comparison neutral">
                                — 0%
                            </span>
                        <?php endif; ?>

                    </div>
                </div>


                <!-- STUDENT VOLUME -->
                <div class="mini-card">
                    <label>Student Volume</label>

                    <div class="card-val">
                        <h3><?= $studentVolume ?></h3>

                        <span class="percentage">
                            <?= number_format($studentPercentage, 1) ?>%
                        </span>
                    </div>
                </div>

                <!-- SCHOOL STAFF -->
                <div class="mini-card">
                    <label>School Staff Volume</label>

                    <div class="card-val">
                        <h3><?= $staffVolume ?></h3>

                        <span class="percentage">
                            <?= number_format($staffPercentage, 1) ?>%
                        </span>
                    </div>
                </div>


                <!-- MEDICINES -->
                <div class="mini-card">
                    <label>Total Medicines Dispensed</label>

                    <div class="card-val">
                        <h3><?= $totalMedicines ?></h3>

                        <?php if ($medicineChange > 0): ?>
                            <span class="comparison positive">
                                ↑ <?= number_format(abs($medicineChange), 1) ?>%
                            </span>
                        <?php elseif ($medicineChange < 0): ?>
                            <span class="comparison negative">
                                ↓ <?= number_format(abs($medicineChange), 1) ?>%
                            </span>
                        <?php else: ?>
                            <span class="comparison neutral">
                                — 0%
                            </span>
                        <?php endif; ?>

                    </div>
                </div>

            </section>

            <!-- CHARTS SECTION -->
            <section class="charts-grid">
                <!-- Daily Volume Chart -->
                <div class="chart-container main-chart">
                    <div class="chart-header">
                        <h3>Daily Consultation Volume (<?= $monthName ?>)</h3>
                        <p>Track peaks and lows throughout the month</p>
                    </div>
                    <div class="canvas-wrapper">
                        <canvas id="dailyVolumeChart"></canvas>
                    </div>
                </div>

                <!-- Patient Type Distribution -->
                <div class="chart-container side-chart">
                    <div class="chart-header">
                        <h3>Patient Type</h3>
                    </div>
                    <div class="canvas-wrapper doughnut-wrapper">
                        <canvas id="patientTypeChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- TOP RANKINGS & RECENT SUMMARY -->
            <section class="data-grid">
                <!-- Top Complaints -->
                <div class="data-card">
                    <h3>Top 5 Complaints</h3>
                    <ul class="rank-list">
                            <?php if (count($topComplaints) > 0): ?>

                                <?php foreach ($topComplaints as $index => $complaint): ?>

                                    <li>
                                        <span>
                                            <?= ($index + 1) ?>.
                                            <?= htmlspecialchars($complaint['complaint']) ?>
                                        </span>

                                        <span class="val">
                                            <?= $complaint['total'] ?>
                                        </span>
                                    </li>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <li>
                                    <span>No complaints recorded</span>
                                    <span class="val">0</span>
                                </li>

                            <?php endif; ?>
                    </ul>
                </div>

                <!-- Top Medicines -->
                <div class="data-card">
                    <h3>Top Dispensed Medicines</h3>
                    <ul class="rank-list">
                        <?php if (count($topMedicines) > 0): ?>

                            <?php foreach ($topMedicines as $index => $medicine): ?>

                                <li>
                                    <span>
                                        <?= ($index + 1) ?>.
                                        <?= htmlspecialchars($medicine['medicine_name']) ?>
                                    </span>

                                    <span class="val">
                                        <?= $medicine['total'] ?>
                                    </span>
                                </li>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <li>
                                <span>No medicines dispensed</span>
                                <span class="val">0</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Log Summary Table -->
                <div class="data-card full-width">
                    <div class="card-title-btn">
                        <h3>Summary of Consultations</h3>
                        <a href="view_consultation.php">View All Logs <i class="fas fa-external-link-alt"></i></a>
                    </div>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient Name</th>
                                <th>Type</th>
                                <th>Complaint</th>
                                <th>Medicine Given</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($summaryResult->num_rows > 0): ?>

                            <?php while ($row = $summaryResult->fetch_assoc()): ?>

                                <tr>

                                    <!-- DATE -->
                                    <td>
                                        <?= date("M d, Y", strtotime($row['date'])) ?>
                                    </td>

                                    <!-- PATIENT NAME -->
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($row['full_name']) ?>
                                        </strong>
                                    </td>

                                    <!-- PATIENT TYPE -->
                                    <td>
                                        <?php
                                        if ($row['patient_type'] === 'student') {
                                            echo 'Student';
                                        } elseif (
                                            $row['patient_type'] === 'tstaff' ||
                                            $row['patient_type'] === 'nstaff'
                                        ) {
                                            echo 'School Staff';
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>

                                    <!-- COMPLAINT -->
                                    <td>
                                        <?= htmlspecialchars($row['complaint'] ?? '—') ?>
                                    </td>

                                    <!-- MEDICINE -->
                                    <td>
                                        <?= htmlspecialchars($row['medicines']) ?>
                                    </td>

                                    <!-- STATUS -->
                                    <td>
                                        <span class="stat-tag completed">
                                            Completed
                                        </span>
                                    </td>

                                </tr>

                                    <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" style="text-align: center;">
                                        No consultation records found for <?= htmlspecialchars($monthName) ?> <?= $selectedYear ?>.
                                    </td>
                                </tr>
                                <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

    <script>
        window.reportData = {
            dailyLabels: <?= json_encode($dailyLabels) ?>,
            dailyData: <?= json_encode($dailyData) ?>,

            studentVolume: <?= $studentVolume ?>,
            teachingStaffVolume: <?= $teachingStaffVolume ?>,
            nonTeachingStaffVolume: <?= $nonTeachingStaffVolume ?>
        };
    </script>

    <script src="../assets/js/reports.js"></script>
    <script src="../includes/includes.js"></script>
</body>
</html>