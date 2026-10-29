
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

$redirect = "../admin/create_personnel.php";

// Return to the Admin form with an inline validation error
function personnelFormError($message, $field, $redirect)
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


// Get form data
$last_name = trim($_POST['last_name'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$middle_name = trim($_POST['middle_name'] ?? '');

$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? '';
$staff_id_number = trim($_POST['staff_id_number'] ?? '');

$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';


// Save non-password values for repopulation
$_SESSION['old_last_name'] = $last_name;
$_SESSION['old_first_name'] = $first_name;
$_SESSION['old_middle_name'] = $middle_name;
$_SESSION['old_email'] = $email;
$_SESSION['old_role'] = $role;
$_SESSION['old_staff_id_number'] = $staff_id_number;


// Validate names
$nameFields = [
    'last_name' => $last_name,
    'first_name' => $first_name,
    'middle_name' => $middle_name
];

foreach ($nameFields as $field => $name) {

    // Middle name is optional
    if ($field === 'middle_name' && $name === '') {
        continue;
    }

    if (!preg_match("/^[A-Za-zÀ-ÿ .'-]+$/", $name)) {
        personnelFormError(
            "Please enter a valid name using letters only.",
            $field,
            $redirect
        );
    }
}


// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    personnelFormError(
        "Please enter a valid email address.",
        'email',
        $redirect
    );
}


// Validate password confirmation
if ($password !== $confirm_password) {
    personnelFormError(
        "Passwords do not match.",
        'confirm_password',
        $redirect
    );
}

// Validate password length
if (strlen($password) < 8) {
    personnelFormError(
        "Password must be at least 8 characters.",
        'password',
        $redirect
    );
}


// Only these personnel roles can be created
if (!in_array($role, ['School Nurse', 'Clinic Staff'], true)) {
    personnelFormError(
        "Invalid personnel role selected.",
        'role',
        $redirect
    );
}


// Validate personnel ID
if (
    $staff_id_number === '' ||
    preg_match('/[A-Za-z]/', $staff_id_number)
) {
    personnelFormError(
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

    personnelFormError(
        "Email is already registered.",
        'email',
        $redirect
    );
}

mysqli_stmt_close($check_email);


// Find the existing personnel record
$check_staff = mysqli_prepare(
    $conn,
    "SELECT id, last_name, first_name, middle_name, position, designation
     FROM staffs
     WHERE staff_id_number = ?"
);

mysqli_stmt_bind_param(
    $check_staff,
    "s",
    $staff_id_number
);

mysqli_stmt_execute($check_staff);

$result = mysqli_stmt_get_result($check_staff);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($check_staff);

    personnelFormError(
        "Personnel ID does not exist in the clinic records.",
        'staff_id_number',
        $redirect
    );
}

$staff = mysqli_fetch_assoc($result);
mysqli_stmt_close($check_staff);

$profile_id = (int) $staff['id'];


// Verify the selected account role against the existing staff record
$profile_position = trim($staff['position'] ?? '');
$profile_designation = trim($staff['designation'] ?? '');

$roleMatches =
    ($role === 'School Nurse' &&
     $profile_position === 'Non-Teaching Staff' &&
     $profile_designation === 'School Nurse') ||

    ($role === 'Clinic Staff' &&
     $profile_position === 'Non-Teaching Staff' &&
     $profile_designation === 'Clinic Staff');

if (!$roleMatches) {
    personnelFormError(
        "The selected account role does not match the personnel record.",
        'role',
        $redirect
    );
}

$profile_last_name = trim($staff['last_name'] ?? '');
$profile_first_name = trim($staff['first_name'] ?? '');
$profile_middle_name = trim($staff['middle_name'] ?? '');

$namesMatch =
    strcasecmp($last_name, $profile_last_name) === 0 &&
    strcasecmp($first_name, $profile_first_name) === 0;

if (
    !$namesMatch ||
    strcasecmp($middle_name, $profile_middle_name) !== 0
) {
    personnelFormError(
        "The name does not match the personnel ID record.",
        'last_name',
        $redirect
    );
}


// Prevent duplicate accounts for the same staff record
$check_account = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE staff_id = ?"
);

mysqli_stmt_bind_param(
    $check_account,
    "i",
    $profile_id
);

mysqli_stmt_execute($check_account);
mysqli_stmt_store_result($check_account);

if (mysqli_stmt_num_rows($check_account) > 0) {
    mysqli_stmt_close($check_account);

    personnelFormError(
        "This personnel already has a registered account.",
        'staff_id_number',
        $redirect
    );
}

mysqli_stmt_close($check_account);


// Build the account name using first and last name, like registration
$full_name = $first_name . ' ' . $last_name;


// Hash the password before storing it
$hashed_password = password_hash($password, PASSWORD_DEFAULT);


// Create the personnel account as already email-verified
$sql = "INSERT INTO users (
            student_id,
            staff_id,
            full_name,
            email,
            password,
            role,
            email_verified,
            verification_token,
            verification_expires
        ) VALUES (
            NULL, ?, ?, ?, ?, ?, 1, NULL, NULL
        )";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "issss",
    $profile_id,
    $full_name,
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
        $_SESSION['old_last_name'],
        $_SESSION['old_first_name'],
        $_SESSION['old_middle_name'],
        $_SESSION['old_email'],
        $_SESSION['old_role'],
        $_SESSION['old_staff_id_number'],
        $_SESSION['error'],
        $_SESSION['error_field']
    );

    $_SESSION['success'] =
        "Personnel account created successfully. The account can now log in.";

    header("Location: " . $redirect);
    exit();

} else {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    personnelFormError(
        "Account creation failed. Please try again.",
        '',
        $redirect
    );
}
