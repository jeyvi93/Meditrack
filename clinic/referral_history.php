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
    FROM referrals
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
// GET REFERRALS
// ==========================

$sql = "
    SELECT *
    FROM referrals
    ORDER BY referred_date DESC, id DESC
    LIMIT $recordsPerPage OFFSET $offset
";

$result = $conn->query($sql);

if (!$result) {
    die("Error loading referral history: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral History | MediTrack</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
     <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/referral_history.css">
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
                    <h1>Referral History</h1>
                    <p>Access and review all previous referral records.</p>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="window.location.href='referral_form.php'">
                        <i class="fas fa-plus"></i> New Referral
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
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Referred Date</th>
                                <th>Full Name</th>
                                <th>Sex</th>
                                <th>Doctor</th>
                                <th>Reason for Referral</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php if (mysqli_num_rows($result) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>
                                <td class="date-cell">
                                    <strong>
                                        <?= date("M d, Y", strtotime($row['referred_date'])) ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="patient-name">
                                        <?= htmlspecialchars($row['full_name']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span>
                                        <?= htmlspecialchars($row['sex']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['doctor_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['reason_for_referral']) ?>
                                </td>

                                <td class="text-right">
                                    <button
                                        class="btn-view"
                                        title="View Full Details"
                                        onclick="openModal(<?= $row['id'] ?>)">
                                        <i class="bx bx-show"></i>
                                    </button>
                                </td>
                            </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" style="text-align: center;">
                                    No referral records found.
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

            </section>
        </main>

 
    </div>
</div>
 <!-- VIEW LOG MODAL -->
<div class="modal-overlay" id="referralModal">
    <div class="modal-container">

        <!-- HEADER -->
        <div class="modal-header">
            <div>
                <h2>Referral Details</h2>
                <p>Complete patient referral information.</p>
            </div>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>

        <!-- BODY -->
        <div class="modal-body">

            <!-- DATE & TIME -->
            <div class="section">
                <h3>Referral Information</h3>
                <div class="grid-2">
                    <div class="info-box">
                        <label>Reffered Date</label>
                        <p id="modalReferredDate">—</p>
                    </div>
                    <div class="info-box">
                        <label>Doctor</label>
                        <p id="modalDoctor">—</p>
                    </div>
                    <div class="info-box full">
                        <label>Reason for Referral</label>
                        <p id="modalReason">—</p>
                    </div>
                </div>
            </div>

            <!-- PATIENT INFO -->
            <div class="section">
                <h3>Patient Information</h3>

                <div class="grid-2">
                    <div class="info-box">
                        <label>Full Name</label>
                        <p id="modalFullName">—</p>
                    </div>

                    <div class="info-box">
                        <label>Sex</label>
                        <p id="modalSex">—</p>
                    </div>

                    <div class="info-box">
                        <label>Date of Birth</label>
                        <p id="modalDob">—</p>
                    </div>

                    <div class="info-box">
                        <label>Contact</label>
                        <p id="modalContact">—</p>
                    </div>

                    <div class="info-box">
                        <label>Address</label>
                        <p id="modalAddress">—</p>
                    </div>
                </div>

            </div>

            <!-- FAMILY INFO -->
            <div class="section">
                <h3>Family Information</h3>
                
                <div class="grid-2">
                    <div class="info-box">
                     <label>Parent Name</label>
                     <p id="modalParentName">—</p>
                    </div>

                    <div class="info-box">
                     <label>Parent's Date of Birth</label>
                     <p id="modalParentDob">—</p>
                    </div>

                    <div class="info-box">
                     <label>Spouse Name</label>
                     <p id="modalSpouseName">—</p>
                    </div>

                    <div class="info-box">
                     <label>Spouse's Date of Birth</label>
                     <p id="modalSpouseDob">—</p>
                    </div>

                </div>

            </div>

            <!-- PHILHEALTH -->
            <div class="section">
                <h3>Philhealth</h3>
                
                <div class="grid-2">
                    <div class="info-box">
                     <label>Member</label>
                     <p id="modalPhilhealthMember">—</p>
                    </div>

                    <div class="info-box">
                     <label>Philhealth Number</label>
                     <p id="modalPhilhealthNumber">—</p>
                    </div>
                </div>
                
            </div>

            <!-- VITALS -->
            <div class="section">
                <h3>Vital Signs</h3>
                
                <div class="grid-2">
                    <div class="info-box">
                     <label>Blood Pressure</label>
                     <p id="modalBP">—</p>
                    </div>

                    <div class="info-box">
                     <label>Weight</label>
                     <p id="modalWeight">—</p>
                    </div>

                    <div class="info-box">
                     <label>Temperature</label>
                     <p id="modalTemperature">—</p>
                    </div>

                </div>

            </div>

            <!-- NURSE NOTES -->
            <div class="section">
                <h3>Nurse's Notes</h3>
                    <div class="info-box full">
                     <p id="modalNurseNotes">—</p>
                    </div>
            </div>
            

        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal()">Close</button>
        </div>

    </div>
</div>
<script src="../includes/includes.js"></script>
<script src="../assets/js/referral_history.js"></script>
</body>
</html>