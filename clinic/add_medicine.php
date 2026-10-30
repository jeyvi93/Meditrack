<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: medicine.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'Administrator') {
    header("Location: ../admin/accounts.php");
    exit();
}

$mode = $_GET['mode'] ?? 'add';
$medicine_id = $_GET['id'] ?? null;
$medicine_name = $_GET['medicine'] ?? '';

$is_restock = ($mode === 'restock');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine | MediTrack</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/add_medicine.css">
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
                    <h1>
                        <?= $is_restock ? 'Restock Medicine' : 'Add New Medicine' ?>
                    </h1>
                    <p>
                        <?= $is_restock
                        ? 'Add a new batch for existing medicine.'
                        : 'Register a new medicine batch into the clinic inventory.' ?>
                    </p>
                </div>
                <a href="medicine.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </header>

            <!-- FORM CONTAINER -->
            <div class="form-wrapper">
                <form class="form-card" id="addMedicineForm">
                    
                    <!-- Basic Information -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-info-circle"></i>
                            <h3>Basic Information</h3>
                        </div>
                        <div class="input-grid three-cols">
                            <div class="input-group">
                                <label for="medName">Medicine Name</label>
                                <input type="text" id="medName" placeholder="e.g. Paracetamol" value="<?= htmlspecialchars($medicine_name) ?>"<?= $is_restock ? 'readonly' : '' ?> required>
                            </div>
                            <div class="input-group">
                                <label for="batchNo">Batch Number</label>
                                <input type="text" id="batchNo" placeholder="e.g. PCM260401" required>
                            </div>
                            <div class="input-group">
                                <label for="supplier">Supplier</label>
                                <input type="text" id="supplier" placeholder="e.g. PharmaCorp Dist." required>
                            </div>
                        </div>
                    </div>

                    <!-- Stock & Dates -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-boxes"></i>
                            <h3>Inventory & Dates</h3>
                        </div>
                        <div class="input-grid three-cols">
                            <div class="input-group">
                                <label for="quantity">Quantity (Units)</label>
                                <input type="number" id="quantity" min="1" placeholder="0" required>
                            </div>
                            <div class="input-group">
                                <label for="lowStockThreshold">Low Stock Threshold</label>
                                <input type="number" id="lowStockThreshold" min="1" placeholder="e.g. 10" required>
                            </div>
                            <div class="input-group">
                                <label for="dateAcquired">Date Acquired</label>
                                <input type="date" id="dateAcquired" required>
                            </div>
                            <div class="input-group">
                                <label for="expiryDate">Expiration Date</label>
                                <input type="date" id="expiryDate" required>
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Submit -->
                    <div class="form-footer">
                        <button type="reset" class="btn-ghost">Clear Fields</button>
                        <button type="submit" class="btn-primary">
                             Save to Inventory
                        </button>
                    </div>
                </form>

                <!-- Helper Alert -->
                <div class="helper-card">
                    <div class="helper-icon"><i class="fas fa-lightbulb"></i></div>
                    <div class="helper-text">
                        <h4>Inventory Tip</h4>
                        <p>Ensure that the Batch Number matches the one printed on the physical packaging for accurate tracking of expirations.</p>
                    </div>
                </div>
            </div>
        </main>

<!-- ADD MEDICINE SUCCESS MODAL -->
<div class="modal" id="addMedicineSuccessModal">

    <div class="modal-overlay"></div>

    <div class="modal-content success-modal-content">

        <div class="success-icon">
            <i class="bx bx-check"></i>
        </div>

        <div class="success-message">

            <h2>Medicine Saved Successfully!</h2>

            <p>
                The medicine batch has been successfully added
                to the inventory.
            </p>

        </div>

        <div class="modal-footer">

            <button
                type="button"
                class="btn-primary"
                onclick="closeAddMedicineSuccessModal()">
                Done
            </button>

        </div>

    </div>

</div>
    
<script src="../includes/includes.js"></script>
<script src="../assets/js/add_medicine.js"></script>
</body>
</html>