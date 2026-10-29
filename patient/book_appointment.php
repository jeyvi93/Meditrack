    <?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    require_once "../config/database.php";

    $user_id = $_SESSION['user_id'];
    $full_name = $_SESSION['full_name'] ?? '';

    $successMessage = $_SESSION['success'] ?? '';
    $errorMessage = $_SESSION['error'] ?? '';

    unset($_SESSION['success']);
    unset($_SESSION['error']);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Book Appointment | MediTrack</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="../assets/css/navbar.css">
        <link rel="stylesheet" href="../assets/css/book_appointment.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    </head>
    <body>

    <div class="bg-gradient"></div>

    <?php include '../includes/navbar.php'; ?>

    <main class="main-wrapper">
        <section class="booking-section">
            
            <div class="page-header">
                <h1>Book <span class="text-accent">Appointment</span></h1>
                <p>Schedule your visit to the Mandaue City College Clinic.</p>
            </div>

            <div class="booking-grid">
                <!-- Left Side: Information Card -->
                <div class="info-side">
                    <div class="status-card">
                        <div class="status-icon"><i class='bx bxs-calendar'></i></div>
                        <div>
                            <h4>Clinic Services Available</h4>
                            <p>Reserve your preferred consultation schedule.</p>
                        </div>
                    </div>
                    
                    <div class="reminder-list">
                        <h5>Important Reminders:</h5>
                        <ul>
                            <li><i class='bx bx-check-double'></i> Please arrive 5 minutes before your slot.</li>
                            <li><i class='bx bx-check-double'></i> Bring your Student/Staff ID for verification.</li>
                            <li><i class='bx bx-check-double'></i> Ensure your contact information is up to date.</li>
                        </ul>
                    </div>
                </div>

                <!-- Right Side: The Actual Form -->
                <form action="../actions/book_appointment.php" method="POST" class="appointment-card" onsubmit="openAppointmentModal(event)">
                    <div class="form-row">
                        <div class="input-group">
                            <label>Logged-in User</label>
                            <div class="input-box disabled">
                                <i class='bx bx-user'></i>
                                <input
                                    type="text"
                                    value="<?= htmlspecialchars($full_name) ?>"
                                    disabled>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Purpose of Visit</label>

                        <div class="input-box">
                            <i class='bx bx-notepad'></i>
                            <textarea
                                name="purpose"
                                rows="2"
                                maxlength="255"
                                placeholder="Please state your main concern or reason for visiting the clinic."
                                required></textarea>
                        </div>

                        <small class="input-hint">
                            Example: Fever and headache, medical consultation, or minor injury.
                        </small>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>Select Date</label>
                            <div class="input-box">
                                <i class='bx bx-calendar'></i>
                                <input
                                    type="date"
                                    name="appointment_date"
                                    min="<?= date('Y-m-d') ?>"
                                    required>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Preferred Time</label>
                            <div class="input-box">
                                <i class='bx bx-time'></i>
                                <select name="appointment_time" required>
                                    <option value="">Select Time</option>
                                    <option value="08:30:00">8:30 AM</option>
                                    <option value="10:00:00">10:00 AM</option>
                                    <option value="13:30:00">1:30 PM</option>
                                    <option value="15:00:00">3:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-booking">
                        Confirm Appointment <i class='bx bx-right-arrow-alt'></i>
                    </button>
                </form>
            </div>
        </section>
    </main>


    <!-- CONFIRM APPOINTMENT MODAL -->
    <div id="confirmAppointmentModal" class="modal-overlay hidden">
        <div class="modal-box">
            <i class='bx bx-calendar-check' style="color: #2563eb;"></i>

            <h3>Confirm Appointment?</h3>

            <p>
                Are you sure you want to submit this appointment request?
            </p>

            <div class="modal-btns">
                <button class="btn-no" onclick="closeAppointmentModal()">
                    Cancel
                </button>

                <button class="btn-yes" onclick="confirmAppointment()">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successAppointmentModal" class="modal-overlay hidden">
        <div class="modal-box success-modal">
            <div class="message-icon success">
                <i class='bx bx-check'></i>
            </div>

            <h3>Appointment Submitted!</h3>

            <p>
                Your appointment request has been submitted successfully.
                Please wait for the clinic to review your request.
            </p>

            <div class="modal-btns single-btn">
                <button class="btn-success" onclick="closeSuccessModal()">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- ERROR MODAL -->
    <div id="errorAppointmentModal" class="modal-overlay hidden">
        <div class="modal-box error-modal">
            <i class='bx bx-error-circle' style="color: #ef4444;"></i>

            <h3>Appointment Notice</h3>

            <p>
                <?= htmlspecialchars($errorMessage) ?>
            </p>

            <div class="modal-btns single-btn">
                <button class="btn-error" onclick="closeErrorModal()">
                    Ok
                </button>
            </div>
        </div>
    </div>


    <script>
    <?php if ($successMessage): ?>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("successAppointmentModal").classList.remove("hidden");
        });
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("errorAppointmentModal").classList.remove("hidden");
        });
    <?php endif; ?>
    </script>

    <script src="../assets/js/book_appointment.js"></script>
    </body>
    </html>