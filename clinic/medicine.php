<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$today = date("Y-m-d");

$sql = "SELECT *
        FROM medicines
        WHERE status = 'Active'
        AND expiry_date >= CURDATE()
        ORDER BY medicine_name ASC";
$all_medicines = mysqli_query($conn, $sql);
$expired_medicines = mysqli_query($conn, "
    SELECT *
    FROM medicines
    WHERE expiry_date < CURDATE()
    AND status != 'Disposed'
    ORDER BY expiry_date ASC
");
$expired_count = mysqli_num_rows($expired_medicines);   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Inventory | MediTrack</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/medicine.css">
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
                    <h1>Medicine Inventory</h1>
                    <p>Track clinical supplies, check expirations, and manage disposals.</p>
                </div>
                    <div class="inventory-summary">
                    <button class="btn-primary" onclick="window.location.href='add_medicine.php'"><i class="fas fa-plus"></i> Add Medicine</button>
                    </div>
            </header>

            <!-- TABS NAVIGATION -->
            <nav class="inventory-tabs">
                <button class="tab-link active" onclick="openTab(event, 'all')">All Medicine</button>
                <button class="tab-link" onclick="openTab(event, 'expired')">
                    Expired 
                    <?php if($expired_count > 0): ?>
                    <span class="tab-badge">
                        <?= $expired_count ?>
                    </span>
                    <?php endif; ?>
                </button>
                <button class="tab-link" onclick="openTab(event, 'disposed')">Disposed Records</button>
                <button class="tab-link" onclick="openTab(event, 'quarantine')"> Quarantine </button>
            </nav>

            <!-- DATA SECTION -->
            <section class="inventory-card">
                <!-- TOOLBAR -->
                <div class="inventory-toolbar">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search medicine, batch, or supplier...">
                    </div>
                <div class="toolbar-right" id="toolbarRight">
                    <div class="filter-group" id="filterGroup">
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="In Stock">In Stock</option>
                            <option value="Low Stock">Low Stock</option>
                            <option value="Near Expiry">Near Expiry</option>
                        </select>
                    </div>
                    <button class="btn-report" onclick="openReportModal()"><i class="fas fa-file-export"></i> Report</button></div>
                </div>

                <!-- ALL INVENTORY TAB -->
                <div id="all" class="tab-pane active">
                    <div class="table-responsive">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Batch No.</th>
                                    <th>Quantity</th>
                                    <th>Date Acquired</th>
                                    <th>Expiration Date</th>
                                    <th>Supplier</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if(mysqli_num_rows($all_medicines) > 0): ?>

                                <?php while($row = mysqli_fetch_assoc($all_medicines)): ?>

                                <?php

                                if(strtotime($row['expiry_date']) < strtotime($today)){
                                    $status = "Expired";
                                }
                                elseif($row['quantity'] <= $row['low_stock_threshold']){
                                    $status = "Low Stock";
                                }
                                elseif(strtotime($row['expiry_date']) <= strtotime("+30 days")){
                                    $status = "Near Expiry";
                                }
                                else{
                                    $status = "In Stock";
                                }

                                ?>

                                <tr>

                                    <td>
                                        <strong><?= htmlspecialchars($row['medicine_name']); ?></strong>
                                    </td>

                                    <td>
                                        <code class="batch">
                                            <?= htmlspecialchars($row['batch_number']); ?>
                                        </code>
                                    </td>

                                    <td><?= $row['quantity']; ?></td>

                                    <td><?= date("M d, Y", strtotime($row['date_acquired'])); ?></td>

                                    <td><?= date("M d, Y", strtotime($row['expiry_date'])); ?></td>

                                    <td><?= htmlspecialchars($row['supplier']); ?></td>

                                    <td>

                                        <span class="status-pill
                                        <?= $status == "In Stock" ? "stat-in-stock" : "" ?>
                                        <?= $status == "Low Stock" ? "stat-low-stock" : "" ?>
                                        <?= $status == "Near Expiry" ? "stat-near-expiry" : "" ?>
                                        <?= $status == "Expired" ? "stat-expired" : "" ?>">
                                            <?= $status ?>
                                        </span>

                                    </td>

                                    <td class="action-cell">

                                        <button class="btn-action primary">
                                            <i class="bx bx-edit"></i>
                                        </button>

                                        <?php if($status == "Expired"): ?>

                                        <button class="btn-dispose">
                                            <i class="bx bx-trash"></i>
                                        </button>

                                        <?php elseif($status == "Low Stock"): ?>

                                        <button class="btn-action info">
                                            <i class="bx bx-package"></i>
                                        </button>

                                        <?php elseif($status == "Near Expiry"): ?>

                                        <button class="btn-dispose">
                                            <i class="bx bx-trash"></i>
                                        </button>

                                        <?php else: ?>

                                        <button class="btn-action warning">
                                            <i class="bx bx-shield-quarter"></i>
                                        </button>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                                    <?php endwhile; ?>

                                    <?php else: ?>

                                <tr>
                                    <td colspan="8" style="text-align:center;">
                                        No medicines found.
                                    </td>
                                </tr>

                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- EXPIRED TAB -->
                <div id="expired" class="tab-pane">
                    <div class="tab-info-banner">
                        <i class="fas fa-info-circle"></i>
                        <span>Note: These items must be marked as disposed before removal from the clinic.</span>
                    </div>
                    <div class="table-responsive">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Batch No.</th>
                                    <th>Quantity</th>
                                    <th>Expired Date</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($expired_medicines) > 0): ?>

                                <?php while($row = mysqli_fetch_assoc($expired_medicines)): ?>

                                <tr class="expired-row">

                                    <td>
                                        <strong><?= htmlspecialchars($row['medicine_name']); ?></strong>
                                    </td>

                                    <td>
                                        <code class="batch">
                                            <?= htmlspecialchars($row['batch_number']); ?>
                                        </code>
                                    </td>

                                    <td>
                                        <?= $row['quantity']; ?>
                                    </td>

                                    <td class="text-danger">
                                        <?= date("M d, Y", strtotime($row['expiry_date'])); ?>
                                    </td>

                                    <td>
                                        <span class="status-pill stat-expired">
                                            Expired
                                        </span>
                                    </td>

                                    <td class="text-right">

                                        <button
                                            class="btn-dispose"
                                            onclick="openExpiredDispose(<?= $row['id']; ?>)">

                                            <i class="bx bx-trash" title="Dispose Medicine"></i>

                                        </button>

                                    </td>

                                </tr>

                                    <?php endwhile; ?>

                                    <?php else: ?>

                                <tr>

                                    <td colspan="6" style="text-align:center;">
                                        No expired medicines found.
                                    </td>

                                </tr>

                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- DISPOSED RECORDS TAB -->
                <div id="disposed" class="tab-pane">
                    <div class="table-responsive">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Batch No.</th>
                                    <th>Quantity Disposed</th>
                                    <th>Disposal Date</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Paracetamol 500mg</strong></td>
                                    <td><code class="batch">B-004</code></td>
                                    <td>120</td>
                                    <td>Oct 10, 2023</td>
                                    <td>Expired</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- QUARANTINE TAB -->
<div id="quarantine" class="tab-pane">
    <div class="tab-info-banner">
        <i class="fas fa-info-circle"></i>
        <span>Note: Quarantined medicines are temporarily restricted and not for dispensing.</span>
    </div>

    <div class="table-responsive">
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Batch No.</th>
                    <th>Quantity</th>
                    <th>Date Acquired</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>

            <tbody>
                <!-- SAMPLE ROW -->
                <tr data-status="Quarantine">
                    <td><strong>Paracetamol</strong></td>
                    <td><code class="batch">B-999</code></td>
                    <td>100</td>
                    <td>Jan 01, 2025</td>
                    <td>
                        <span class="status-pill stat-quarantine">
                            Quarantine
                        </span>
                    </td>
                    <td class="text-right">
                        <button class="btn-action primary">
                            <i class="bx bx-transfer-alt" title="Return to Stock"></i>
                        </button>

                        <button class="btn-action danger" onclick="openQuarantineDispose()">
                            <i class="bx bx-trash" title="Dispose"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

            </section>
        </main>

    
<!-- EDIT MEDICINE MODAL -->
<div class="modal" id="editModal">
    <div class="modal-overlay" onclick="closeEditModal()"></div>

    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Medicine</h2>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form class="modal-body">
            <div class="form-group">
                <label>Medicine Name</label>
                <input type="text" value="Amoxicillin">
            </div>

            <div class="form-group">
                <label>Batch No.</label>
                <input type="text" value="B-205">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" value="450">
                </div>

                <div class="form-group">
                    <label>Supplier</label>
                    <input type="text" value="PharmaPlus">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date Acquired</label>
                    <input type="date">
                </div>

                <div class="form-group">
                    <label>Expiration Date</label>
                    <input type="date">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!--DISPOSE MODAL-->
<div class="modal" id="disposeModal">

    <div class="modal-overlay"
         onclick="closeDisposeModal()"></div>

    <div class="modal-content dispose-modal-content">

        <div class="modal-header">
            <h2 id="disposeModalTitle">
                Dispose Batch
            </h2>

            <button class="modal-close"
                    onclick="closeDisposeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body">

            <!-- warning -->
            <div class="dispose-warning">
                <i class="fas fa-triangle-exclamation"></i>
                <p>
                    This batch will be removed from active inventory.
                </p>
            </div>

            <!-- info card -->
            <div class="dispose-info-card">

                <div class="dispose-info-row">
                    <span>Medicine</span>
                    <strong id="disposeMedicine">
                        Paracetamol 500mg
                    </strong>
                </div>

                <div class="dispose-info-row">
                    <span>Batch No.</span>
                    <strong id="disposeBatch">
                        PAR-2026-001
                    </strong>
                </div>

                <div class="dispose-info-row">
                    <span>Quantity</span>
                    <strong id="disposeQty">
                        25 Tablets
                    </strong>
                </div>

            </div>

            <!-- fixed reason -->
            <div class="dispose-fixed-reason"
                 id="disposeFixedReasonContainer">

                <label>Disposal Reason</label>

                <div class="dispose-reason-badge"
                     id="disposeFixedReason">

                    Will Not Be Consumed Before Expiry

                </div>

            </div>

            <!-- dropdown -->
            <div class="dispose-reason-group"
                 id="disposeReasonGroup">

                <label>Disposal Reason</label>

                <select id="disposeReasonSelect">
                    <option>Select reason</option>
                    <option>Damaged Packaging</option>
                    <option>Contamination</option>
                    <option>Product Recall</option>
                    <option>Failed Inspection</option>
                </select>

            </div>
        </div>

        <div class="modal-footer">

            <button class="btn-secondary"
                    onclick="closeDisposeModal()">
                Cancel
            </button>

            <button class="btn-confirm-dispose">
                Confirm Disposal
            </button>

        </div>

    </div>

</div>

<!--INVENTORY REPORT MODAL-->
<div class="modal" id="reportModal">

    <div class="modal-overlay" onclick="closeReportModal()"></div>

    <div class="modal-content">

        <div class="modal-header">
            <h2>Inventory Report</h2>

            <button class="modal-close" onclick="closeReportModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body">

            <!-- SUMMARY -->
            <div class="report-summary">

                <div class="report-card">
                    <span>Total Medicines</span>
                    <strong id="repTotal">0</strong>
                </div>

                <div class="report-card danger">
                    <span>Expired</span>
                    <strong id="repExpired">0</strong>
                </div>

                <div class="report-card warning">
                    <span>Low Stock</span>
                    <strong id="repLow">0</strong>
                </div>

                <div class="report-card near">
                    <span>Near Expiry</span>
                    <strong id="repNear">0</strong>
                </div>

            </div>

            <!-- DETAILS -->
            <div class="report-details">

                <h4>Inventory Breakdown</h4>

                <ul id="reportList"></ul>

            </div>

        </div>

        <div class="modal-footer">

            <button class="btn-secondary" onclick="closeReportModal()">
                Close
            </button>

        </div>

    </div>

</div>

    <script src="../includes/includes.js"></script>
    <script src="../assets/js/medicine.js"></script>
</body>
</html>