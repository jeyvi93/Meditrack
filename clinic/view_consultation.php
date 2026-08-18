<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}


// ==========================
// PAGINATION
// ==========================

$recordsPerPage = 10;

$currentPage = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

$offset = ($currentPage - 1) * $recordsPerPage;


// ==========================
// TOTAL RECORDS
// ==========================

$countSql = "
    SELECT COUNT(*) AS total
    FROM consultations
";

$countResult = $conn->query($countSql);

$totalRecords = $countResult->fetch_assoc()['total'];

$totalPages = ceil($totalRecords / $recordsPerPage);


// Prevent invalid page
if ($totalPages > 0 && $currentPage > $totalPages) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $recordsPerPage;
}


// ==========================
// GET CONSULTATIONS
// ==========================

$sql = "
    SELECT
        id,
        date,
        time,
        patient_type,
        full_name,
        department,
        course,
        year_level,
        position
    FROM consultations
    ORDER BY date DESC, time DESC
    LIMIT $recordsPerPage OFFSET $offset
";

$result = $conn->query($sql);

if (!$result) {
    die("Error loading consultation logs: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation History | MediTrack</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
     <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/view_consultation.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>

    <!-- SIDEBAR -->
    <?php include "../includes/sidebar.php"; ?>
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="bg-gradient"></div>

            <header class="content-header">
                <div class="title-area">
                    <h1>Consultation History</h1>
                    <p>Access and review all previous clinical consultation records.</p>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="window.location.href='consultation.php'">
                        <i class="fas fa-plus"></i> New Consultation
                    </button>
                </div>
            </header>

            <!-- LOGS DATA CARD -->
            <section class="logs-card">
                
                <!-- TOOLBAR -->
                <div class="logs-toolbar">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search by name, department, or details...">
                    </div>
                    
                    <div class="filter-group">
                        <select class="filter-select" id="patientFilter">
                            <option value="all">All Patient Types</option>
                            <option value="student">Student</option>
                            <option value="tstaff">Teaching Staff</option>
                            <option value="nstaff">Non-Teaching Staff</option>
                        </select>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Full Name</th>
                                <th>Patient Type</th>
                                <th>Department</th>
                                <th>Details</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>
                                <td class="date-cell">
                                    <strong>
                                        <?= date("M d, Y", strtotime($row['date'])) ?>
                                    </strong>

                                    <span>
                                        <?= date("h:i A", strtotime($row['time'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="patient-name">
                                        <?= htmlspecialchars($row['full_name']) ?>
                                    </span>
                                </td>

                                <td>

                                    <?php if ($row['patient_type'] === 'student'): ?>

                                        <span class="type-pill pill-student">
                                            Student
                                        </span>

                                    <?php elseif ($row['patient_type'] === 'tstaff'): ?>

                                        <span class="type-pill pill-staff">
                                            Teaching Staff
                                        </span>

                                    <?php elseif ($row['patient_type'] === 'nstaff'): ?>

                                        <span class="type-pill pill-staff">
                                            Non-Teaching Staff
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= htmlspecialchars($row['department'] ?? '—') ?>
                                </td>

                                <td>

                                    <?php if ($row['patient_type'] === 'student'): ?>

                                        <?= htmlspecialchars($row['course'] ?? '—') ?>

                                        <?php if (!empty($row['year_level'])): ?>
                                            - <?= htmlspecialchars($row['year_level']) ?>
                                        <?php endif; ?>

                                    <?php else: ?>

                                        <?= htmlspecialchars($row['position'] ?? '—') ?>

                                    <?php endif; ?>

                                </td>

                                <td class="text-right">

                                    <button
                                        class="btn-view"
                                        onclick="openModal(<?= $row['id'] ?>)"
                                        title="View Full Log"
                                        >
                                        <i class="bx bx-show"></i>
                                    </button>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" style="text-align:center;">
                            No consultation records found.
                            </td>
                        </tr>

                    <?php endif; ?>

                        </tbody>
                    </table>
                </div>

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
                            <i class="fas fa-chevron-left"></i>
                        </a>

                        <?php else: ?>

                        <button
                            class="btn-page"
                            disabled
                            >
                            <i class="fas fa-chevron-left"></i>
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
                                <i class="fas fa-chevron-right"></i>
                            </a>

                        <?php else: ?>

                            <button
                                class="btn-page"
                                disabled
                                >
                                <i class="fas fa-chevron-right"></i>
                            </button>

                        <?php endif; ?>

                    </div>

                    <?php endif; ?>

                </div>

            </section>
        </main>

    <!-- VIEW LOG MODAL -->
<div class="modal-overlay" id="viewLogModal">
    <div class="modal-container">

        <!-- HEADER -->
        <div class="modal-header">
            <div>
                <h2>Medical Log Details</h2>
                <p>Complete patient consultation record</p>
            </div>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>

        <!-- BODY -->
        <div class="modal-body">

            <!-- DATE & TIME -->
            <div class="section">
                <h3>Record Information</h3>
                <div class="grid-2">
                    <div class="info-box">
                        <label>Date</label>
                        <p id="modalDate">—</p>
                    </div>
                    <div class="info-box">
                        <label>Time</label>
                        <p id="modalTime">—</p>
                    </div>
                </div>
            </div>

            <!-- PATIENT INFO -->
            <div class="section">
                <h3>Patient Information</h3>

                <div class="grid-2">
                    <div class="info-box">
                        <label>Patient Type</label>
                        <span id="modalPatientType">—</span>
                    </div>

                    <div class="info-box">
                        <label>Full Name</label>
                        <p id="modalFullName">—</p>
                    </div>

                    <div class="info-box">
                        <label>Age / Sex</label>
                        <p id="modalAgeSex">—</p>
                    </div>

                    <div class="info-box">
                        <label>Contact</label>
                        <p id="modalContact">—</p>
                    </div>

                    <div class="info-box">
                        <label>Department</label>
                        <p id="modalDepartment">—</p>
                    </div>

                    <div class="info-box" id="modalCourseYearBox">
                        <label>Course & Year</label>
                        <p id="modalCourseYear">—</p>
                    </div>
                </div>

                <div class="info-box full" id="modalPositionBox">
                    <label>Position (if staff)</label>
                    <p id="modalPosition" class="text-muted">N/A</p>
                </div>
            </div>

            <!-- CLINICAL ASSESSMENT -->
            <div class="section">
                <h3>Clinical Assessment</h3>

                <div class="info-box full">
                    <label>Complaint / Symptoms</label>
                    <p id="modalComplaint">—</p>
                </div>

                <div class="info-box full">
                    <label>Treatment Given</label>
                    <p id="modalTreatment">—</p>
                </div>

                <div class="info-box full">
                    <label>Remarks (Optional)</label>
                    <p id="modalRemarks" class="text-muted">—</p>
                </div>
            </div>

            <!-- MEDICINE DISPENSED -->
            <div class="section">
                <h3>Medicine Dispensed</h3>

                <div class="table-mini" id="modalMedicines">
                    <div class="table-row header">
                        <span>Medicine Name</span>
                        <span>Quantity</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal()">Close</button>
        </div>

    </div>
</div>

</div>
<script src="../includes/includes.js"></script>
<script src="../assets/js/view_consultation.js"></script>
</body>
</html>