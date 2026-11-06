<?php
session_start();

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
$errorField = $_SESSION['error_field'] ?? '';
$oldLastName = $_SESSION['old_last_name'] ?? '';
$oldFirstName = $_SESSION['old_first_name'] ?? '';
$oldMiddleName = $_SESSION['old_middle_name'] ?? '';
$oldEmail = $_SESSION['old_email'] ?? '';
$oldRole = $_SESSION['old_role'] ?? '';
$oldIdNumber = $_SESSION['old_id_number'] ?? '';

unset($_SESSION['old_last_name']);
unset($_SESSION['old_first_name']);
unset($_SESSION['old_middle_name']);
unset($_SESSION['old_email']);
unset($_SESSION['old_role']);
unset($_SESSION['old_id_number']);

unset($_SESSION['success']);
unset($_SESSION['error']);
unset($_SESSION['error_field']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | MediTrack</title>
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/guest_navbar.css">
    <link rel="stylesheet" href="../assets/css/register.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="bg-gradient"></div>

    <?php
    $guestNav = [
        'secondary_label' => 'Home',
        'secondary_href' => '../index.php',
        'primary_label' => 'Login',
        'primary_href' => '../login.php',
        'logo_src' => '../assets/images/med.png'
    ];

    include '../includes/guest_navbar.php';
    ?>

    <div class="main-wrapper">

        <section class="register-section">
            <div class="register-card">
                
                <!-- Left Side: Decorative Image (Hidden on Mobile) -->
                <div class="register-info">
                <div class="info-content">
                    <span class="badge">Join MediTrack</span>
                    <h2>Fast-track your clinic appointments.</h2>
                    <p>Creating an account allows you to book appointments, view appoinment status, and receive clinic notifications.</p>
                    
                    <ul class="benefit-list">
                        <li><i class="fa-solid fa-circle-check"></i> Online Appointment Booking</li>
                        <li><i class="fa-solid fa-circle-check"></i> Real-time Appointment Status</li>
                        <li><i class="fa-solid fa-circle-check"></i> Secure Data Privacy</li>
                    </ul>
                </div>
            </div>

                <!-- Right Side: The Form -->
                <div class="register-form-container">
                    <div class="form-header">
                        <h1>Create Account</h1>
                        <p>Fill in your details to get started.</p>
                    </div>

                    <form action="../actions/register_process.php" method="POST" class="registration-form" novalidate>
                        <div class="form-grid">
                            <div class="input-group">
                                <label for="last_name">Last Name</label>
                                <div class="input-box">
                                    <i class="fas fa-user"></i>
                                    <input
                                        type="text"
                                        id="last_name"
                                        name="last_name"
                                        placeholder="Enter last name"
                                        value="<?php echo htmlspecialchars($oldLastName); ?>"
                                        pattern="[A-Za-zÀ-ÿ .'-]+"
                                        oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                                        title="Name must contain letters only"
                                        required>
                                </div>
                                <?php if ($errorField === 'last_name'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label for="first_name">First Name</label>
                                <div class="input-box">
                                    <i class="fas fa-user"></i>
                                    <input
                                        type="text"
                                        id="first_name"
                                        name="first_name"
                                        placeholder="Enter first name"
                                        value="<?php echo htmlspecialchars($oldFirstName); ?>"
                                        pattern="[A-Za-zÀ-ÿ .'-]+"
                                        oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                                        title="Name must contain letters only"
                                        required>
                                </div>
                                <?php if ($errorField === 'first_name'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group full-width" >
                                <label for="middle_name">Middle Name <span class="middle-name-note">(Leave blank if none)</span></label>
                                <div class="input-box">
                                    <i class="fas fa-user"></i>
                                    <input
                                        type="text"
                                        id="middle_name"
                                        name="middle_name"
                                        placeholder="Enter middle name"
                                        value="<?php echo htmlspecialchars($oldMiddleName); ?>"
                                        pattern="[A-Za-zÀ-ÿ .'-]*"
                                        oninput="this.value = this.value.replace(/[0-9]/g, '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())"
                                        title="Name must contain letters only">
                                </div>
                                <?php if ($errorField === 'middle_name'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group full-width">
                                <label>Email Address</label>
                                <div class="input-box">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email"  name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($oldEmail); ?>" required>
                                </div>
                                <?php if ($errorField === 'email'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label>Role</label>
                                <div class="input-box">
                                    <i class="fas fa-id-badge"></i>
                                    <select name="role" required>
                                        <option value="" disabled <?php echo $oldRole === '' ? 'selected' : ''; ?>>Select Role</option>
                                        <option value="Student" <?php echo $oldRole === 'Student' ? 'selected' : ''; ?>>Student</option>
                                        <option value="Teaching Staff" <?php echo $oldRole === 'Teaching Staff' ? 'selected' : ''; ?>>Teaching Staff</option>
                                        <option value="Non-Teaching Staff" <?php echo $oldRole === 'Non-Teaching Staff' ? 'selected' : ''; ?>>Non-Teaching Staff</option>
                                                                            </select>
                                </div>
                                <?php if ($errorField === 'role'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label>ID Number</label>
                                <div class="input-box">
                                    <i class="fas fa-id-card"></i>
                                    <input type="text"
                                    name="id_number"
                                    placeholder="Enter ID Number"
                                    maxlength="20"
                                    pattern="[0-9!@#$%^&*()_+=\-./ ]+"
                                    oninput="this.value = this.value.replace(/[A-Za-z]/g, '')"
                                    title="ID number can contain numbers and symbols only."
                                    value="<?php echo htmlspecialchars($oldIdNumber); ?>"
                                    required>
                                </div>
                                <?php if ($errorField === 'id_number'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label>Password</label>
                                <div class="input-box password-box">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="password" id="password" placeholder="Enter Password" required>
                                    <i class="bx bx-show password-toggle" onclick="togglePassword('password', this)"></i>   
                                </div>
                                <?php if ($errorField === 'password'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group">
                                <label>Confirm Password</label>
                                <div class="input-box password-box">
                                    <i class="fas fa-shield-halved"></i>
                                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter Password" required>
                                    <i class="bx bx-show password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                                </div>
                                <?php if ($errorField === 'confirm_password'): ?>
                                    <span class="field-error"><?php echo htmlspecialchars($errorMessage); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Complete Registration</button>
                    </form>

                    <!-- REGISTRATION PROCESSING MODAL -->
                    <div id="registerProcessingModal" class="modal-overlay">
                        <div class="success-modal">
                            <div class="processing-spinner"></div>
                            <h2>Creating Account...</h2>
                            <p>Please wait while we process your registration.</p>
                        </div>
                    </div>

                    <p class="mobile-login-hint">Already have an account? <a href="../login.php">Login here</a></p>
                </div>
            </div>
        </section>
    </div>

    <!-- SUCCESS MODAL -->
        <div id="successModal" class="modal-overlay hidden">
            <div class="success-modal">

                <div class="success-icon">
                    <i class="bx bxs-envelope"></i>
                </div>

                <h2>Check Your Email</h2>

                <p>
                    A confirmation link has been sent to your email.
                    Please check your inbox to verify your account.
                </p>

                <button class="btn-success" onclick="goToLogin()">
                    OK
                </button>

            </div>
        </div>

    <script>

        const successModal = document.getElementById("successModal");


        function goToLogin() {
            window.location.href = "../login.php";
        }


        <?php if (!empty($successMessage)): ?>

        successModal.classList.remove("hidden");

        setTimeout(() => {
            successModal.classList.add("show");
        }, 50);

        <?php endif; ?>

        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);

            if (!input) return;

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bx-show");
                icon.classList.add("bx-hide");
            } else {
                input.type = "password";
                icon.classList.remove("bx-hide");
                icon.classList.add("bx-show");
            }
        }

        // REAL-TIME REGISTRATION VALIDATION
        const registrationForm = document.querySelector(".registration-form");

        // REMOVE EMOJIS FROM ALL TEXT FIELDS
        if (registrationForm) {
            registrationForm.addEventListener("input", function (event) {

                const input = event.target;

                if (!input.matches('input[type="text"], input[type="email"], textarea')) {
                    return;
                }

                input.value = input.value.replace(
                    /[\p{Extended_Pictographic}\p{Emoji_Presentation}\p{Emoji_Modifier}\p{Regional_Indicator}\uFE0F\u200D\u20E3]/gu,
                    ""
                );

            });
        }

if (registrationForm) {

    const fields = [
        "last_name",
        "first_name",
        "middle_name",
        "email",
        "role",
        "id_number",
        "password",
        "confirm_password"
    ];

    const touchedFields = new Set();

    const errorMessages = {
        last_name: "Please enter a valid name using letters only.",
        first_name: "Please enter a valid name using letters only.",
        middle_name: "Please enter a valid name using letters only.",
        email: "Please enter a valid email address.",
        role: "Please select a valid role.",
        id_number: "ID number can contain numbers and symbols only.",
        password: "Password must be at least 8 characters.",
        confirm_password: "Passwords do not match."
    };

    function validateField(fieldName) {

        const input = registrationForm.elements[fieldName];
        const group = input.closest(".input-group");
        const inputBox = group.querySelector(".input-box");

        let error = "";

        if (fieldName === "password") {

            if (input.value.length < 8) {
                error = errorMessages.password;
            }

        } else if (fieldName === "confirm_password") {

            const password = registrationForm.elements.password;

            if (
                input.value === "" ||
                input.value !== password.value
            ) {
                error = errorMessages.confirm_password;
            }

        } else if (
            fieldName === "middle_name" &&
            input.value.trim() === ""
        ) {

            error = "";

        } else if (!input.checkValidity()) {

            error = errorMessages[fieldName];
        }

        let errorElement = group.querySelector(".field-error");

        if (!errorElement) {
            errorElement = document.createElement("span");
            errorElement.className = "field-error";
            inputBox.insertAdjacentElement("afterend", errorElement);
        }

        errorElement.textContent = error;
        errorElement.hidden = !error;

        inputBox.classList.toggle("invalid", error !== "");

        return error === "";
    }

    // Preserve existing PHP-generated errors
    fields.forEach(fieldName => {

        const input = registrationForm.elements[fieldName];
        const group = input.closest(".input-group");
        const existingError = group.querySelector(".field-error");

        if (existingError && existingError.textContent.trim() !== "") {
            input.closest(".input-box").classList.add("invalid");
            touchedFields.add(fieldName);
        } else if (existingError) {
            existingError.hidden = true;
        }

    });

    // Validate while typing or changing selections
    fields.forEach(fieldName => {

        const input = registrationForm.elements[fieldName];

        input.addEventListener("input", function () {

            touchedFields.add(fieldName);
            validateField(fieldName);

            // Recheck confirmation when the password changes
            if (
                fieldName === "password" &&
                touchedFields.has("confirm_password")
            ) {
                validateField("confirm_password");
            }

        });

        input.addEventListener("change", function () {

            touchedFields.add(fieldName);
            validateField(fieldName);

        });

        input.addEventListener("blur", function () {

            touchedFields.add(fieldName);
            validateField(fieldName);

        });

    });

    // REGISTRATION PROCESSING
        registrationForm.addEventListener("submit", function (event) {

            let formIsValid = true;
            let firstInvalidField = null;

            fields.forEach(fieldName => {

                touchedFields.add(fieldName);

                const isValid = validateField(fieldName);

                if (!isValid) {

                    formIsValid = false;

                    if (!firstInvalidField) {
                        firstInvalidField = registrationForm.elements[fieldName];
                    }
                }

            });

            if (!formIsValid) {

                event.preventDefault();

                if (firstInvalidField) {
                    firstInvalidField.focus();
                }

                return;
            }

            const modal = document.getElementById("registerProcessingModal");
            const registerButton = registrationForm.querySelector(".btn-submit");

            if (modal && registerButton) {
                modal.classList.add("show");
                registerButton.disabled = true;
                registerButton.textContent = "Creating Account...";
            }

        });

    }

    </script>
</body>
</html>