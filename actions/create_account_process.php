<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Administrator-only access
if (($_SESSION['role'] ?? '') !== 'Administrator') {
    http_response_code(403);
    exit('Access denied. Admin privileges are required.');
}

require_once "../config/database.php";

$redirect = "../admin/create_account.php";

// Return to the Admin form with an inline validation error
function accountFormError($message, $field, $redirect)
{
    $_SESSION['error'] = $message;
    $_SESSION['error_field'] = $field;

    header("Location: " . $redirect);
    exit();
}

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $redirect);
    exit();
}

// Validate CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrf_token) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrf_token)
) {
    accountFormError(
        "Invalid form submission. Please try again.",
        '',
        $redirect
    );
}

// Get form data
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');
$id_number = trim($_POST['staff_id_number'] ?? '');

$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';


// Save non-password values for repopulation
$_SESSION['old_email'] = $email;
$_SESSION['old_role'] = $role;
$_SESSION['old_id_number'] = $id_number;


// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    accountFormError(
        "Please enter a valid email address.",
        'email',
        $redirect
    );
}


// Validate password confirmation
if ($password !== $confirm_password) {
    accountFormError(
        "Passwords do not match.",
        'confirm_password',
        $redirect
    );
}

// Validate password length
if (strlen($password) < 8) {
    accountFormError(
        "Password must be at least 8 characters.",
        'password',
        $redirect
    );
}


// Validate account type
$allowedRoles = [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff',
    'School Nurse',
    'Clinic Staff'
];

if (!in_array($role, $allowedRoles, true)) {
    accountFormError(
        "Invalid account type selected.",
        'role',
        $redirect
    );
}

// Validate ID number
if (
    $id_number === '' ||
    preg_match('/[A-Za-z]/', $id_number)
) {
    accountFormError(
        "ID number can contain numbers and symbols only.",
        'staff_id_number',
        $redirect
    );
}


// Check whether email is already registered
$check_email = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE email = ?"
);

mysqli_stmt_bind_param($check_email, "s", $email);
mysqli_stmt_execute($check_email);
mysqli_stmt_store_result($check_email);

if (mysqli_stmt_num_rows($check_email) > 0) {
    mysqli_stmt_close($check_email);

    accountFormError(
        "Email is already registered.",
        'email',
        $redirect
    );
}

mysqli_stmt_close($check_email);

// Determine which profile table to use
if ($role === 'Student') {

    // Find existing student record
    $check_profile = mysqli_prepare(
        $conn,
        "SELECT id, first_name, last_name
         FROM students
         WHERE student_id_number = ?
         AND is_archived = 0"
    );

    mysqli_stmt_bind_param($check_profile, "s", $id_number);

    $profile_type = 'student';

} else {

    // Find existing staff record
    $check_profile = mysqli_prepare(
        $conn,
        "SELECT id, first_name, last_name, middle_name, position, designation
         FROM staffs
         WHERE staff_id_number = ?
         AND is_archived = 0"
    );

    mysqli_stmt_bind_param($check_profile, "s", $id_number);

    $profile_type = 'staff';
}

mysqli_stmt_execute($check_profile);

$result = mysqli_stmt_get_result($check_profile);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($check_profile);

    accountFormError(
        "No matching active record was found for this ID number.",
        'staff_id_number',
        $redirect
    );
}

$profile = mysqli_fetch_assoc($result);
mysqli_stmt_close($check_profile);

$profile_id = (int) $profile['id'];

// Verify the selected account type against the existing staff record
if ($profile_type === 'staff') {

    $profile_position = trim($profile['position'] ?? '');
    $profile_designation = trim($profile['designation'] ?? '');

    $roleMatches =
        ($role === 'Teaching Staff' &&
        $profile_position === 'Teaching Staff') ||

        ($role === 'Non-Teaching Staff' &&
        $profile_position === 'Non-Teaching Staff') ||

        ($role === 'School Nurse' &&
        $profile_position === 'Non-Teaching Staff') ||

        ($role === 'Clinic Staff' &&
        $profile_position === 'Non-Teaching Staff');

    if (!$roleMatches) {
        accountFormError(
            "The selected account type does not match the existing profile.",
            'role',
            $redirect
        );
    }
}

// Prevent duplicate accounts for the same profile
if ($profile_type === 'student') {

    $check_account = mysqli_prepare(
        $conn,
        "SELECT id FROM users WHERE student_id = ?"
    );

} else {

    $check_account = mysqli_prepare(
        $conn,
        "SELECT id FROM users WHERE staff_id = ?"
    );
}

mysqli_stmt_bind_param(
    $check_account,
    "i",
    $profile_id
);

mysqli_stmt_execute($check_account);
mysqli_stmt_store_result($check_account);

if (mysqli_stmt_num_rows($check_account) > 0) {
    mysqli_stmt_close($check_account);

    accountFormError(
        "This person already has a registered account.",
        'staff_id_number',
        $redirect
    );
}

mysqli_stmt_close($check_account);


// Retrieve the name from the existing profile
$last_name = trim($profile['last_name'] ?? '');
$first_name = trim($profile['first_name'] ?? '');
$middle_name = trim($profile['middle_name'] ?? '');

// Hash the password before storing it
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Link the account to the correct profile
$linked_student_id = ($profile_type === 'student') ? $profile_id : null;
$linked_staff_id = ($profile_type === 'staff') ? $profile_id : null;

$sql = "INSERT INTO users (
    student_id,
    staff_id,
    email,
    password,
    role,
    email_verified
) VALUES (?, ?, ?, ?, ?, 1)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    mysqli_close($conn);

    accountFormError(
        "Unable to prepare the account creation request.",
        '',
        $redirect
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "iisss",
    $linked_student_id,
    $linked_staff_id,
    $email,
    $hashed_password,
    $role
);

// Save the account
if (mysqli_stmt_execute($stmt)) {


    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Clear old values and previous validation errors
    unset(
        $_SESSION['old_email'],
        $_SESSION['old_role'],
        $_SESSION['old_id_number'],
        $_SESSION['old_staff_id_number'],
        $_SESSION['error'],
        $_SESSION['error_field']
    );

   $_SESSION['success'] = "Account created successfully.";

    header("Location: " . $redirect);
    exit();

} else {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    accountFormError(
        "Account creation failed. Please try again.",
        '',
        $redirect
    );
}
