<?php

require_once "../config/database.php";

$token = trim($_GET["token"] ?? "");

if (empty($token)) {

    header("Location: reset_error.php?error=invalid");
    exit();

}

$sql = "SELECT id, reset_expires
        FROM users
        WHERE reset_token = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {

    header("Location: reset_error.php?error=invalid");
    exit();

}

$user = mysqli_fetch_assoc($result);

if (
    empty($user["reset_expires"]) ||
    strtotime($user["reset_expires"]) < time()
) {

    header("Location: reset_error.php?error=expired");
    exit();

}

$error = $_GET["error"] ?? "";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reset Password | MediTrack</title>

    <!-- Reset Password Styles -->
    <link
        rel="stylesheet"
        href="../assets/css/reset_password.css"
    >

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet"
    >

    <!-- Boxicons -->
    <link
        href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="bg-gradient"></div>


<!-- DESKTOP NAVBAR -->

<nav class="navbar">

    <div class="logo">

        <img
            src="../assets/images/med.png"
            alt="MediTrack Logo"
        >

    </div>


    <div class="nav-auth">

        <a
            href="register.php"
            class="register-link"
        >
            Register
        </a>


        <a
            href="../login.php"
            class="btn-login"
        >
            Login
        </a>

    </div>

</nav>


<!-- MOBILE TOP BAR -->

<nav class="mobile-top-bar">

    <a
        href="../login.php"
        class="back-btn-mobile"
    >

        <i class='bx bx-chevron-left'></i>

    </a>


    <span class="nav-title">
        Security
    </span>


    <div class="spacer-nav"></div>

</nav>


<!-- MAIN -->

<main class="main-wrapper">

    <section class="booking-grid">


        <!-- LEFT SIDE -->

        <div class="info-side">


            <div class="status-card">

                <div class="status-icon">

                    <i class='bx bxs-lock-alt'></i>

                </div>


                <div>

                    <h4>
                        Security First
                    </h4>

                    <p>
                        Create a new password to secure your MediTrack account.
                    </p>

                </div>

            </div>


            <div class="reminder-list">

                <h5>
                    Password Tips:
                </h5>


                <ul>

                    <li>

                        <i class='bx bx-check-shield'></i>

                        Use at least 8 characters.

                    </li>


                    <li>

                        <i class='bx bx-check-shield'></i>

                        Mix letters, numbers, and symbols.

                    </li>


                    <li>

                        <i class='bx bx-check-shield'></i>

                        Avoid using your school ID as password.

                    </li>

                </ul>

            </div>

        </div>


        <!-- RESET PASSWORD CARD -->

        <div class="appointment-card">


            <div class="profile-header-inner">


                <div class="icon-circle-large">

                    <i class='bx bx-lock-open-alt'></i>

                </div>


                <div class="header-text">

                    <h2>
                        Reset Password
                    </h2>

                    <p>
                        Create a new password for your MediTrack account.
                    </p>

                </div>

            </div>


            <form
                id="resetPasswordForm"
                action="../api/auth/update_password.php"
                method="POST"
                class="password-form"
                novalidate
            >


                <input
                    type="hidden"
                    name="token"
                    value="<?= htmlspecialchars($token) ?>"
                >


                <div class="single-col">


                    <!-- NEW PASSWORD -->

                    <div class="info-group">

                        <label>
                            New Password
                        </label>


                        <div class="input-box">

                            <i class='bx bx-lock-alt'></i>

                            <input
                                type="password"
                                id="new_password"
                                name="password"
                                placeholder="Enter new password"
                                required
                            >

                            <i
                                class='bx bx-show password-toggle'
                                onclick="togglePassword('new_password', this)"
                            ></i>

                        </div>

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="info-group">

                        <label>
                            Confirm New Password
                        </label>


                        <div class="input-box">

                            <i class='bx bx-shield-alt-2'></i>

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Repeat new password"
                                required
                            >

                            <i
                                class='bx bx-show password-toggle'
                                onclick="togglePassword('confirm_password', this)"
                            ></i>

                        </div>

                    </div>


                    <!-- ERROR MESSAGES -->

                    <?php if ($error === "password_mismatch"): ?>

                        <div class="password-error-message">
                            <i class='bx bx-error-circle'></i>
                            <span>Passwords do not match.</span>
                        </div>

                    <?php elseif ($error === "required_fields"): ?>

                        <div class="password-error-message">
                            <i class='bx bx-error-circle'></i>
                            <span>Please complete all required fields.</span>
                        </div>

                    <?php elseif ($error === "password_length"): ?>

                        <div class="password-error-message">
                            <i class='bx bx-error-circle'></i>
                            <span>Password must be at least 8 characters.</span>
                        </div>

                    <?php elseif ($error === "update_failed"): ?>

                        <div class="password-error-message">
                            <i class='bx bx-error-circle'></i>
                            <span>Unable to reset your password. Please try again.</span>
                        </div>

                    <?php endif; ?>


                </div>


                <!-- ACTION -->

                <div class="profile-actions">

                    <button
                        type="submit"
                        class="btn-save-profile"
                    >

                        Reset Password

                    </button>

                </div>


            </form>

        </div>

    </section>

</main>

<!-- PASSWORD PROCESSING MODAL -->
<div id="passwordProcessingModal" class="modal-overlay hidden">
    <div class="modal-box password-processing-box">
        <div class="processing-spinner"></div>

        <h3>Resetting Password...</h3>

        <p>Please wait while we securely update your password.</p>
    </div>
</div>

<script>

function togglePassword(inputId, icon) {

    const input = document.getElementById(inputId);

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

// PASSWORD PROCESSING MODAL

document.addEventListener("DOMContentLoaded", function () {

    const passwordForm = document.querySelector(".password-form");

    // ==========================
    // REAL-TIME PASSWORD VALIDATION
    // ==========================

    const passwordInputs = {
        new: document.getElementById("new_password"),
        confirm: document.getElementById("confirm_password")
    };

    function validatePasswordField(input) {
        const box = input.closest(".input-box");
        const group = input.closest(".info-group");

        if (!box || !group) return true;

        let error = "";

        if (input.value.trim() === "") {
            error = "This field is required.";
        } else if (input.id === "new_password" && input.value.length < 8) {
            error = "Password must be at least 8 characters long.";
        } else if (
            input.id === "confirm_password" &&
            input.value !== passwordInputs.new.value
        ) {
            error = "Passwords do not match.";
        }

        let errorElement = group.querySelector(".password-field-error");

        if (!errorElement) {
            errorElement = document.createElement("span");
            errorElement.className = "password-field-error";
            box.insertAdjacentElement("afterend", errorElement);
        }

        errorElement.textContent = error;
        errorElement.hidden = !error;

        box.classList.toggle("invalid", error !== "");

        return error === "";
    }

    Object.values(passwordInputs).forEach(input => {
        input.addEventListener("input", function () {
            validatePasswordField(input);

            // Recheck confirmation when the new password changes
            if (input.id === "new_password" && passwordInputs.confirm.value !== "") {
                validatePasswordField(passwordInputs.confirm);
            }
        });

        input.addEventListener("blur", function () {
            validatePasswordField(input);
        });
    });

    if (!passwordForm) return;

    passwordForm.addEventListener("submit", function (e) {

        let formIsValid = true;
        let firstInvalidInput = null;

        Object.values(passwordInputs).forEach(input => {

            if (!validatePasswordField(input)) {
                formIsValid = false;

                if (!firstInvalidInput) {
                    firstInvalidInput = input;
                }
            }

        });

        if (!formIsValid) {
            e.preventDefault();
            firstInvalidInput.focus();
            return;
        }

        const processingModal = document.getElementById("passwordProcessingModal");
        const submitButton = passwordForm.querySelector(".btn-save-profile");

        if (!processingModal) return;

        processingModal.classList.remove("hidden");

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = "Resetting Password...";
        }

    });

});

</script>
</body>

</html>