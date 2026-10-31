<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: ../clinic/students.php");
    exit();
}

require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

$role = $_SESSION['role'] ?? '';

if (in_array($role, [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff'
], true)) {

    header("Location: ../patient/view_consultation.php");
    exit();

}

if ($role === 'Administrator') {

    header("Location: ../admin/accounts.php");
    exit();

}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Student ID (Hidden Input)
    $student_id = $_POST['student_id_number'];

    // Name Information

    $last_name = trim($_POST['last_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');

    // Personal Information
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $department = $_POST['department'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    // Health Information
    $blood_type = trim($_POST['blood_type'] ?? '') !== ''
    ? trim($_POST['blood_type'])
    : null;

    $height = trim($_POST['height'] ?? '') !== ''
        ? trim($_POST['height'])
        : null;

    $weight = trim($_POST['weight'] ?? '') !== ''
        ? trim($_POST['weight'])
        : null;

    $allergies = trim($_POST['allergies'] ?? '') !== ''
        ? trim($_POST['allergies'])
        : null;

    $medical_history = trim($_POST['medical_history'] ?? '') !== ''
        ? trim($_POST['medical_history'])
        : null;

    $current_medications = trim($_POST['current_medications'] ?? '') !== ''
        ? trim($_POST['current_medications'])
        : null;

    $vaccines = trim($_POST['vaccines'] ?? '') !== ''
        ? trim($_POST['vaccines'])
        : null;

    // Insurance Information
    $philhealth_number = trim($_POST['philhealth_number'] ?? '') !== ''
        ? trim($_POST['philhealth_number'])
        : null;

    $insurance_number = trim($_POST['insurance_number'] ?? '') !== ''
        ? trim($_POST['insurance_number'])
        : null;

    $insurance_company = trim($_POST['insurance_company'] ?? '') !== ''
        ? trim($_POST['insurance_company'])
        : null;

    // Emergency Contact
    $emergency_name = $_POST['emergency_name'];
    $emergency_relationship = $_POST['emergency_relationship'];
    $emergency_contact = $_POST['emergency_contact'];

    // ==========================
    // GET ORIGINAL STUDENT INFORMATION
    // ==========================

    $oldSql = "
        SELECT
            last_name, first_name, middle_name,
            date_of_birth, sex, course, year_level,
            department, contact_number, address,
            blood_type, height, weight, allergies,
            medical_history, current_medications, vaccines,
            philhealth_number, insurance_number,
            insurance_company, emergency_name,
            emergency_relationship, emergency_contact
        FROM students
        WHERE student_id_number = ?
    ";

    $oldStmt = mysqli_prepare($conn, $oldSql);

    if (!$oldStmt) {
        $_SESSION['error'] = "Failed to retrieve original student information.";
        mysqli_close($conn);
        header("Location: ../clinic/students.php");
        exit();
    }

    mysqli_stmt_bind_param($oldStmt, "s", $student_id);
    mysqli_stmt_execute($oldStmt);

    $oldResult = mysqli_stmt_get_result($oldStmt);
    $oldStudent = mysqli_fetch_assoc($oldResult);

    mysqli_stmt_close($oldStmt);

    if (!$oldStudent) {
        $_SESSION['error'] = "Student record not found.";
        mysqli_close($conn);
        header("Location: ../clinic/students.php");
        exit();
    }
    // ==========================
    // COMPARE OLD AND NEW VALUES
    // ==========================

    $fieldLabels = [
        'last_name' => 'last name',
        'first_name' => 'first name',
        'middle_name' => 'middle name',
        'date_of_birth' => 'date of birth',
        'sex' => 'sex',
        'course' => 'course',
        'year_level' => 'year level',
        'department' => 'department',
        'contact_number' => 'contact number',
        'address' => 'address',
        'blood_type' => 'blood type',
        'height' => 'height',
        'weight' => 'weight',
        'allergies' => 'allergies',
        'medical_history' => 'medical history',
        'current_medications' => 'current medications',
        'vaccines' => 'vaccines',
        'philhealth_number' => 'PhilHealth number',
        'insurance_number' => 'insurance number',
        'insurance_company' => 'insurance company',
        'emergency_name' => 'emergency contact name',
        'emergency_relationship' => 'emergency relationship',
        'emergency_contact' => 'emergency contact number'
    ];

    $newValues = [
        'last_name' => $last_name,
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'date_of_birth' => $date_of_birth,
        'sex' => $sex,
        'course' => $course,
        'year_level' => $year_level,
        'department' => $department,
        'contact_number' => $contact_number,
        'address' => $address,
        'blood_type' => $blood_type,
        'height' => $height,
        'weight' => $weight,
        'allergies' => $allergies,
        'medical_history' => $medical_history,
        'current_medications' => $current_medications,
        'vaccines' => $vaccines,
        'philhealth_number' => $philhealth_number,
        'insurance_number' => $insurance_number,
        'insurance_company' => $insurance_company,
        'emergency_name' => $emergency_name,
        'emergency_relationship' => $emergency_relationship,
        'emergency_contact' => $emergency_contact
    ];

    $changes = [];

    /* Sensitive fields: record the field changed without exposing its values */
    $sensitiveFields = [
        'contact_number',
        'address',
        'blood_type',
        'height',
        'weight',
        'allergies',
        'medical_history',
        'current_medications',
        'vaccines',
        'philhealth_number',
        'insurance_number',
        'insurance_company',
        'emergency_name',
        'emergency_relationship',
        'emergency_contact'
    ];

    foreach ($fieldLabels as $field => $label) {

        $oldValue = trim((string) ($oldStudent[$field] ?? ''));
        $newValue = trim((string) ($newValues[$field] ?? ''));

        // Normalize decimal values
        if (in_array($field, ['height', 'weight'], true)) {

            $oldValue = $oldValue !== ''
                ? rtrim(rtrim(number_format((float)$oldValue, 2, '.', ''), '0'), '.')
                : '';

            $newValue = $newValue !== ''
                ? rtrim(rtrim(number_format((float)$newValue, 2, '.', ''), '0'), '.')
                : '';
        }

        if ($oldValue !== $newValue) {

            if (in_array($field, $sensitiveFields, true)) {
                $changes[] = "Edited the {$label}";
            } else {
                $changes[] = "{$label} {$oldValue} --> {$newValue}";
            }
        }
    }

    $sql = "UPDATE students SET

        last_name = ?,
        first_name = ?,
        middle_name = ?,
        date_of_birth = ?,
        sex = ?,
        course = ?,
        year_level = ?,
        department = ?,
        contact_number = ?,
        address = ?,
        blood_type = ?,
        height = ?,
        weight = ?,
        allergies = ?,
        medical_history = ?,
        current_medications = ?,
        vaccines = ?,
        philhealth_number = ?,
        insurance_number = ?,
        insurance_company = ?,
        emergency_name = ?,
        emergency_relationship = ?,
        emergency_contact = ?
        WHERE student_id_number = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        header("Location: ../clinic/students.php");
        exit();
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssissssddsssssssssss",
        $last_name,
        $first_name,
        $middle_name,
        $date_of_birth,
        $sex,
        $course,
        $year_level,
        $department,
        $contact_number,
        $address,
        $blood_type,
        $height,
        $weight,
        $allergies,
        $medical_history,
        $current_medications,
        $vaccines,
        $philhealth_number,
        $insurance_number,
        $insurance_company,
        $emergency_name,
        $emergency_relationship,
        $emergency_contact,
        $student_id
    );

    if (mysqli_stmt_execute($stmt)) {

        /* Clinic Activity Log */
        if (!empty($changes)) {

            $studentName = trim(
                $last_name . ', ' . $first_name . ' ' . $middle_name
            );

            $description = "Updated student profile for {$studentName}. "
                        . "Student ID: {$student_id}. "
                        . implode('; ', $changes) . ".";

            logClinicActivity(
                $conn,
                'Updated',
                'Students',
                $description
            );
        }

        $_SESSION['success'] = "Student Profile Updated!";
        $_SESSION['success_message'] = "The student record has been updated successfully.";

    } else {

        $_SESSION['error'] = "Failed to update student profile.";

    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../clinic/students.php");
    exit();

} else {

    header("Location: ../clinic/students.php");
    exit();

}