<?php
session_start();

require_once "../config/database.php";
require_once "../includes/email_sender.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $id_number = trim($_POST['id_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    // Save non-password inputs for form repopulation
    $_SESSION['old_email'] = $email;
    $_SESSION['old_role'] = $role;
    $_SESSION['old_id_number'] = $id_number;

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        $_SESSION['error_field'] = 'email';

        header("Location: ../patient/register.php");
        exit();
    }


    // Validate password
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        $_SESSION['error_field'] = 'confirm_password';

        header("Location: ../patient/register.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters.";
        $_SESSION['error_field'] = 'password';

        header("Location: ../patient/register.php");
        exit();
    }


    // Validate role
    if (!in_array($role, ['Student', 'Teaching Staff', 'Non-Teaching Staff'])) {
        $_SESSION['error'] = "Invalid role selected.";
        $_SESSION['error_field'] = 'role';

        header("Location: ../patient/register.php");
        exit();
    }


    // Validate ID number
    if ($id_number === '' || preg_match('/[A-Za-z]/', $id_number)) {
        $_SESSION['error'] = "ID number can contain numbers and symbols only.";
        $_SESSION['error_field'] = 'id_number';

        header("Location: ../patient/register.php");
        exit();
    }


    // Check if email is already registered
    $check_email = mysqli_prepare(
        $conn,
        "SELECT id FROM users WHERE email = ?"
    );

    mysqli_stmt_bind_param($check_email, "s", $email);
    mysqli_stmt_execute($check_email);
    mysqli_stmt_store_result($check_email);

    if (mysqli_stmt_num_rows($check_email) > 0) {

        $_SESSION['error'] = "Email is already registered.";
        $_SESSION['error_field'] = 'email';

        mysqli_stmt_close($check_email);
        mysqli_close($conn);

        header("Location: ../patient/register.php");
        exit();
    }

    mysqli_stmt_close($check_email);


    // Variable for existing profile
    $profile_id = null;


    // STUDENT REGISTRATION
    if ($role === 'Student') {

        $check = mysqli_prepare(
            $conn,
            "SELECT id, last_name, first_name
            FROM students
            WHERE student_id_number = ? AND is_archived = 0"
        );

        mysqli_stmt_bind_param($check, "s", $id_number);
        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) == 0) {

            $_SESSION['error'] = "Student ID does not exist in the clinic records.";
            $_SESSION['error_field'] = 'id_number';

            mysqli_stmt_close($check);
            mysqli_close($conn);

            header("Location: ../patient/register.php");
            exit();
        }

        $student = mysqli_fetch_assoc($result);

        $profile_id = $student['id'];
        $profile_last_name = trim($student['last_name']);
        $profile_first_name = trim($student['first_name']);

        mysqli_stmt_close($check);
        
        // Check if student already has an account
        $check_account = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE student_id = ?"
        );

        mysqli_stmt_bind_param($check_account, "i", $profile_id);
        mysqli_stmt_execute($check_account);
        mysqli_stmt_store_result($check_account);

        if (mysqli_stmt_num_rows($check_account) > 0) {

            $_SESSION['error'] = "This student already has a registered account.";
            $_SESSION['error_field'] = 'id_number';

            mysqli_stmt_close($check_account);
            mysqli_close($conn);

            header("Location: ../patient/register.php");
            exit();
        }

        mysqli_stmt_close($check_account);
    }


    // STAFF REGISTRATION
    if ($role === 'Teaching Staff' || $role === 'Non-Teaching Staff') {

        $check = mysqli_prepare(
            $conn,
            "SELECT id, last_name, first_name
            FROM staffs
            WHERE staff_id_number = ? AND is_archived = 0"
        );

        mysqli_stmt_bind_param($check, "s", $id_number);
        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) == 0) {

            $_SESSION['error'] = "Staff ID does not exist in the clinic records.";
            $_SESSION['error_field'] = 'id_number';

            mysqli_stmt_close($check);
            mysqli_close($conn);

            header("Location: ../patient/register.php");
            exit();
        }

        $staff = mysqli_fetch_assoc($result);

        $profile_id = $staff['id'];
        $profile_last_name = trim($staff['last_name']);
        $profile_first_name = trim($staff['first_name']);

        mysqli_stmt_close($check);

        // Check if staff already has an account
        $check_account = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE staff_id = ?"
        );

        mysqli_stmt_bind_param($check_account, "i", $profile_id);
        mysqli_stmt_execute($check_account);
        mysqli_stmt_store_result($check_account);

        if (mysqli_stmt_num_rows($check_account) > 0) {

            $_SESSION['error'] = "This staff member already has a registered account.";
            $_SESSION['error_field'] = 'id_number';

            mysqli_stmt_close($check_account);
            mysqli_close($conn);

            header("Location: ../patient/register.php");
            exit();
        }

        mysqli_stmt_close($check_account);
    }

    // Build account name using the existing clinic profile
    $full_name = $profile_first_name . ' ' . $profile_last_name;

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate email verification token
    $verification_token = bin2hex(random_bytes(32));
    $verification_expires = date("Y-m-d H:i:s", time() + 900);

    // Insert user account
    if ($role === 'Student') {

        $sql = "INSERT INTO users (
            student_id,
            staff_id,
            email,
            password,
            role,
            email_verified,
            verification_token,
            verification_expires
        ) VALUES (
            ?, NULL, ?, ?, ?, 0, ?, ?
        )";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "isssss",
            $profile_id,
            $email,
            $hashed_password,
            $role,
            $verification_token,
            $verification_expires
        );
    } else {

        $sql = "INSERT INTO users (
            student_id,
            staff_id,
            email,
            password,
            role,
            email_verified,
            verification_token,
            verification_expires
        ) VALUES (
            NULL, ?, ?, ?, ?, 0, ?, ?
        )";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "isssss",
            $profile_id,
            $email,
            $hashed_password,
            $role,
            $verification_token,
            $verification_expires
        );
    }


        // Execute registration
        if (mysqli_stmt_execute($stmt)) {

        
        // Create verification link dynamically
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
             $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );

        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Derive the project base path from this script's location.
        $projectPath = str_replace(
            '\\',
            '/',
            rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\')
        );

        $verification_link = $scheme . '://' . $host .
            $projectPath .
            '/api/auth/verify_email.php?token=' .
            urlencode($verification_token);


        
        // Send verification email
        try {

            $emailBody = "
                <h2>MediTrack</h2>

                <p>Hello <strong>" . htmlspecialchars($full_name) . "</strong>,</p>

                <p>
                    Your MediTrack account has been successfully registered.
                </p>

                <p>
                    To complete your registration and activate your account, please verify
                    your email address by clicking the button below.
                </p>

                <p style='margin: 30px 0;'>
                    <a href='" . $verification_link . "'
                    style='
                    display:inline-block;
                    padding:12px 22px;
                    background:#800000;
                    color:white;
                    text-decoration:none;
                    border-radius:6px;
                    font-weight:bold;'>
                    Verify Email Address
                    </a>
                </p>

                <p>
                    This verification link will expire in <strong>15 minutes</strong>.
                </p>

                <p>
                    If you did not create a MediTrack account, please disregard
                    this message.
                </p>

                <p style='margin-top: 30px;'>
                    Regards,<br>
                    <strong>MediTrack</strong>
                </p>
            ";

            sendMediTrackEmail(
                $email,
                $full_name,
                "Verify Your MediTrack Account",
                $emailBody
            );

            $_SESSION['success'] =
                "Registration successful. A verification link has been sent to your email.";

        } catch (\Throwable $e) {

            error_log("MediTrack registration email error: " . $e->getMessage());

            // Remove the account if email could not be sent
            mysqli_query(
                $conn,
                "DELETE FROM users WHERE email = '" .
                mysqli_real_escape_string($conn, $email) . "'"
            );

            $_SESSION['error'] =
                "Registration failed because the verification email could not be sent.";

            mysqli_stmt_close($stmt);
            mysqli_close($conn);

            header("Location: ../patient/register.php");
            exit();
        }


        // Clear saved form values after successful registration
        unset($_SESSION['old_email']);
        unset($_SESSION['old_role']);
        unset($_SESSION['old_id_number']);

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: ../patient/register.php");
        exit();

    } else {

        $_SESSION['error'] = "Registration failed. Please try again.";

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: ../patient/register.php");
        exit();
    }

} else {

    header("Location: ../patient/register.php");
    exit();
}

?>
