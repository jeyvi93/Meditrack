<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/database.php";
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
                <span class="value">0</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-light-teal">
                <i class='bx bxs-calendar-exclamation'></i>
            </div>
            <div class="stat-data">
                <span class="label">Pending Appointments</span>
                <span class="value">4</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-light-orange">
                <i class='bx bxs-user-detail'></i>
            </div>
            <div class="stat-data">
                <span class="label">Consultations Today</span>
                <span class="value">12</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-light-purple">
                <i class='bx bxs-capsule'></i>
            </div>
            <div class="stat-data">
                <span class="label">Medicine Alerts</span>
                <span class="value">08</span>
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

        <div class="activity-item">

            <div class="activity-icon approved">
                <i class='bx bx-check-circle'></i>
            </div>

            <div class="activity-details">
                <h4>Appointment Approved</h4>
                <p>John Doe's appointment was approved.</p>
                <span>09:30 AM • Checkup</span>
            </div>

        </div>



        <div class="activity-item">

            <div class="activity-icon pending">
                <i class='bx bx-calendar'></i>
            </div>

            <div class="activity-details">
                <h4>New Appointment Request</h4>
                <p>Maria Santos requested a consultation.</p>
                <span>Today • General Checkup</span>
            </div>

        </div>



        <div class="activity-item">

            <div class="activity-icon cancel">
                <i class='bx bx-x-circle'></i>
            </div>

            <div class="activity-details">
                <h4>Appointment Cancelled</h4>
                <p>Juan Dela Cruz cancelled an appointment.</p>
                <span>Yesterday • Medical Consultation</span>
            </div>

        </div>



        <div class="activity-item">

            <div class="activity-icon medicine">
                <i class='bx bx-capsule'></i>
            </div>

            <div class="activity-details">
                <h4>Medicine Updated</h4>
                <p>Paracetamol inventory was updated.</p>
                <span>2 hours ago</span>
            </div>

        </div>


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

<script src="../includes/includes.js"></script>
<script src="../assets/js/dashboard.js"></script>

</body>
</html>