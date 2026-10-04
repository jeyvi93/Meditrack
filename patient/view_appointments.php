<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';

unset($_SESSION['success'], $_SESSION['error']);

require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT 
        id,
        appointment_date,
        appointment_time,
        purpose,
        status,
        reason,
        note
    FROM appointments
    WHERE user_id = ?
    ORDER BY appointment_date ASC, appointment_time ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$appointments = [];

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

$stmt->close();

$pendingAppointments = [];
$approvedAppointments = [];
$completedAppointments = [];
$cancelledAppointments = [];

foreach ($appointments as $appointment) {
    $status = strtolower(trim($appointment['status'] ?? ''));

    if ($status === 'pending') {
        $pendingAppointments[] = $appointment;
    } elseif ($status === 'approved') {
        $approvedAppointments[] = $appointment;
    } elseif ($status === 'completed') {
        $completedAppointments[] = $appointment;
    } elseif ($status === 'rejected' || $status === 'cancelled') {
        $cancelledAppointments[] = $appointment;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | MediTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/view_appointments.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<div class="bg-gradient"></div>

<?php include '../includes/navbar.php'; ?>

<main class="main-wrapper">
    <section class="booking-section">
        
        <div class="page-header">
            <h1>My <span class="text-accent">Appointments</span></h1>
            <p>View and manage your clinic visits and medical requests.</p>
        </div>

        <div class="booking-grid">
            <!-- Left Side: Information Card (Same as Book Page) -->
            <div class="info-side">
                <div class="status-card">
                    <div class="status-icon"><i class='bx bxs-time-five'></i></div>
                    <div>
                        <h4>Appointment Status</h4>
                        <p>Track your requests from pending to completion.</p>
                    </div>
                </div>
                
                <div class="reminder-list">
                    <h5>Quick Guide:</h5>
                    <ul>
                        <li><i class='bx bx-check-double'></i> <b>Pending:</b> Waiting for clinic review.</li>
                        <li><i class='bx bx-check-double'></i> <b>Approved:</b> Your slot is confirmed.</li>
                        <li><i class='bx bx-check-double'></i> <b>History:</b> Record of past visits.</li>
                    </ul>
                </div>
            </div>

            <!-- Right Side: The Content (Your Tab Contents) -->
            <div class="appointment-card">
                
                <!-- TAB NAVIGATION -->
                <div class="appointment-tabs">
                    <button class="tab-btn active" onclick="switchTab(event, 'pending')">Pending</button>
                    <button class="tab-btn" onclick="switchTab(event, 'approved')">Approved</button>
                    <button class="tab-btn" onclick="switchTab(event, 'completed')">History</button>
                    <button class="tab-btn" onclick="switchTab(event, 'cancelled')">Rejected / Cancelled</button>
                </div>

                <!-- PENDING -->
                <div id="pending" class="tab-content active">

                    <div class="appointments-list-wrapper">

                        <?php if (empty($pendingAppointments)): ?>

                            <div class="empty-appointments">
                                <div class="empty-icon pending-icon">
                                    <i class='bx bx-time-five'></i>
                                </div>

                                <h3>No Pending Appointments</h3>

                                <p>
                                    You don't have any appointment requests waiting for review.
                                    New requests will appear here after you submit an appointment.
                                </p>

                                <a href="book_appointment.php" class="empty-action">
                                    <i class='bx bx-plus'></i>
                                    Book an Appointment
                                </a>
                            </div>

                        <?php else: ?>

                            <?php foreach ($pendingAppointments as $appointment): ?>

                                <div class="inner-card">

                                    <div class="card-date-info">
                                        <span class="day">
                                            <?= date('d', strtotime($appointment['appointment_date'])) ?>
                                        </span>

                                        <span class="month">
                                            <?= strtoupper(date('M', strtotime($appointment['appointment_date']))) ?>
                                        </span>
                                    </div>

                                    <div class="card-text">

                                        <span class="status-pill pending">
                                            Pending
                                        </span>

                                        <h3>
                                            <?= htmlspecialchars($appointment['purpose']) ?>
                                        </h3>

                                        <p>
                                            <i class='bx bx-time'></i>
                                            <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                        </p>

                                    </div>

                                    <button
                                        class="btn-cancel-mini"
                                        onclick="openModal(<?= (int)$appointment['id'] ?>)">
                                        Cancel
                                    </button>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

                <!-- APPROVED -->
                <div id="approved" class="tab-content">

                    <div class="appointments-list-wrapper">

                        <?php if (empty($approvedAppointments)): ?>

                            <div class="empty-appointments">
                                <div class="empty-icon">
                                    <i class='bx bx-calendar-check'></i>
                                </div>

                                <h3>No Approved Appointments</h3>

                                <p>
                                    You don't have any approved appointments yet.
                                    Once the clinic approves your request, it will appear here.
                                </p>

                                <a href="book_appointment.php" class="empty-action">
                                    <i class='bx bx-plus'></i>
                                    Book an Appointment
                                </a>
                            </div>

                        <?php else: ?>

                            <?php foreach ($approvedAppointments as $appointment): ?>

                                <div class="inner-card">

                                    <div class="card-date-info confirmed">
                                        <span class="day">
                                            <?= date('d', strtotime($appointment['appointment_date'])) ?>
                                        </span>

                                        <span class="month">
                                            <?= strtoupper(date('M', strtotime($appointment['appointment_date']))) ?>
                                        </span>
                                    </div>

                                    <div class="card-text">

                                        <span class="status-pill confirmed">
                                            Approved
                                        </span>

                                        <h3>
                                            <?= htmlspecialchars($appointment['purpose']) ?>
                                        </h3>

                                        <p>
                                            <i class='bx bx-time'></i>
                                            <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                        </p>

                                    </div>

                                    <button
                                        class="btn-cancel-mini"
                                        onclick="openModal(<?= (int)$appointment['id'] ?>)">
                                        Cancel
                                    </button>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

                <!-- COMPLETED (TABLE) -->
                <div id="completed" class="tab-content">

                    <div class="table-container">

                        <table class="appointment-table">

                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (empty($completedAppointments)): ?>

                                    <tr>
                                        <td colspan="3" style="text-align: center;">
                                            No completed appointments yet.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($completedAppointments as $appointment): ?>

                                        <tr>
                                            <td data-label="Date">
                                                <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?>
                                            </td>

                                            <td data-label="Purpose">
                                                <?= htmlspecialchars($appointment['purpose']) ?>
                                            </td>

                                            <td data-label="Status">
                                                <span class="status-pill completed">
                                                    Completed
                                                </span>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- REJECTED / CANCELLED -->
                <div id="cancelled" class="tab-content">

                    <div class="table-container">

                        <table class="appointment-table cancelled-table">

                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Purpose</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (empty($cancelledAppointments)): ?>

                                    <tr>
                                        <td colspan="4" style="text-align: center;">
                                            No rejected or cancelled appointments.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($cancelledAppointments as $appointment): ?>

                                        <tr>

                                            <td data-label="Date">
                                                <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?>
                                            </td>

                                            <td data-label="Purpose">
                                                <?= htmlspecialchars($appointment['purpose']) ?>
                                            </td>

                                            <td data-label="Status">

                                                <?php if (strtolower($appointment['status']) === 'rejected'): ?>

                                                    <span class="status-pill cancelled">
                                                        Rejected
                                                    </span>

                                                <?php else: ?>

                                                    <span class="status-pill cancelled">
                                                        Cancelled
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td data-label="Details">
                                                <div class="appointment-details">

                                                    <div class="reason-text">
                                                        <strong>Reason:</strong>
                                                        <?= !empty($appointment['reason'])
                                                            ? htmlspecialchars($appointment['reason'])
                                                            : 'No reason provided.' ?>
                                                    </div>

                                                    <?php if (!empty($appointment['note'])): ?>

                                                        <div class="note-text">
                                                            <strong>Note:</strong>
                                                            <?= htmlspecialchars($appointment['note']) ?>
                                                        </div>

                                                    <?php endif; ?>

                                                </div>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        </div>
    </section>
</main>

<form
    id="cancelAppointmentForm"
    method="POST"
    action="../actions/student_cancel_appointment.php"
    style="display: none;"
>
    <input
        type="hidden"
        name="appointment_id"
        id="cancelAppointmentId"
    >

    <input
        type="hidden"
        name="reason"
        id="cancelAppointmentReason"
    >
</form>

<!-- CANCEL MODAL -->
<div id="cancelModal" class="modal-overlay hidden">
    <div class="modal-box">

        <i class='bx bx-error-circle'></i>

        <h3>Cancel Appointment?</h3>

        <p>
            This action cannot be undone. Please provide a reason for cancellation.
        </p>

        <div class="cancel-reason">
            <label for="cancelReason">Reason for cancellation</label>

            <select id="cancelReason">
                <option value="">Select a reason</option>
                <option value="Schedule conflict">Schedule conflict</option>
                <option value="No longer needed">No longer needed</option>
                <option value="Feeling better">Feeling better</option>
                <option value="Booked by mistake">Booked by mistake</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="cancel-other hidden" id="cancelOtherWrapper">
            <label for="otherReason">Please specify</label>

            <textarea
                id="otherReason"
                rows="3"
                placeholder="Enter your reason..."
            ></textarea>
        </div>

        <div class="modal-btns">
            <button class="btn-no" onclick="closeModal()">
                No, Keep it
            </button>

            <button class="btn-logout-confirm" onclick="confirmCancel()">
                Yes, Cancel
            </button>
        </div>

    </div>
</div>

<!-- SUCCESS MODAL -->
<div id="successModal" class="modal-overlay hidden">
    <div class="modal-box success-modal">

        <i class='bx bx-check-circle' style="color: #10b981;"></i>

        <h3>Appointment Cancelled!</h3>

        <p>
            Your appointment has been cancelled successfully.
        </p>

        <div class="modal-btns single-btn">
            <button class="btn-success" onclick="closeSuccessModal()">
                Done
            </button>
        </div>

    </div>
</div>

<script>
    function switchTab(evt, tabName) {
        var i, tabcontent, tablinks;

        tabcontent = document.getElementsByClassName("tab-content");

        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        tablinks = document.getElementsByClassName("tab-btn");

        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className =
                tablinks[i].className.replace(" active", "");
        }

        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }


    let selectedAppointmentId = null;


    function openModal(appointmentId) {

        selectedAppointmentId = appointmentId;

        // Reset reason fields every time the modal opens
        document.getElementById('cancelReason').value = '';
        document.getElementById('otherReason').value = '';
        document.getElementById('cancelOtherWrapper')
            .classList.add('hidden');

        document.getElementById('cancelModal')
            .classList.remove('hidden');
    }


    function closeModal() {

        selectedAppointmentId = null;

        document.getElementById('cancelModal')
            .classList.add('hidden');
    }


    // Show "Other" text box
    document.getElementById('cancelReason')
        .addEventListener('change', function () {

            const otherWrapper =
                document.getElementById('cancelOtherWrapper');

            if (this.value === 'Other') {
                otherWrapper.classList.remove('hidden');
            } else {
                otherWrapper.classList.add('hidden');
            }
        });


    function confirmCancel() {

        if (!selectedAppointmentId) {
            return;
        }

        const selectedReason =
            document.getElementById('cancelReason').value;

        if (!selectedReason) {
            alert('Please select a reason for cancellation.');
            return;
        }

        let reason = selectedReason;

        if (selectedReason === 'Other') {

            reason =
                document.getElementById('otherReason')
                .value
                .trim();

            if (!reason) {
                alert('Please specify your reason for cancellation.');
                return;
            }
        }

        // Put the appointment ID into the hidden form
        document.getElementById('cancelAppointmentId').value =
            selectedAppointmentId;

        // Put the cancellation reason into the hidden form
        document.getElementById('cancelAppointmentReason').value =
            reason;

        // Submit to student_cancel_appointment.php
        document.getElementById('cancelAppointmentForm').submit();
    }


    function openLogoutModal() {
        document.getElementById('logoutModal')
            .classList.remove('hidden');
    }


    function closeLogoutModal() {
        document.getElementById('logoutModal')
            .classList.add('hidden');
    }


    function confirmLogout() {
        window.location.href = "../auth/logout.php";
    }

    function openSuccessModal() {
        document.getElementById('successModal')
            .classList.remove('hidden');
    }

    function closeSuccessModal() {
        document.getElementById('successModal')
            .classList.add('hidden');
    }

    <?php if ($successMessage): ?>
        document.addEventListener("DOMContentLoaded", function () {
            openSuccessModal();
        });
    <?php endif; ?>
</script>
</body>
</html>