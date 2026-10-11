<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: ../clinic/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get logged-in user
    $user_id = $_SESSION['user_id'];

    // Get form data
    $referred_date = $_POST['referred_date'];
    $doctor_name = $_POST['doctor_name'];
    $reason_for_referral = $_POST['reason_for_referral'];

    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];

    $parent_name = $_POST['parent_name'];
    $parent_dob = $_POST['parent_dob'];
    $spouse_name = $_POST['spouse_name'];
    $spouse_dob = $_POST['spouse_dob'];

    // Convert Yes/No to 1/0 for the database
    $is_philhealth_member = ($_POST['philhealth'] === 'yes') ? 1 : 0;

    $philhealth_number = $_POST['philhealth_number'];

    $blood_pressure = $_POST['blood_pressure'];
    $weight = $_POST['weight'];
    $temperature = $_POST['temperature'];
    $nurse_notes = $_POST['nurse_notes'];

    // Insert referral
    $sql = "INSERT INTO referrals (
        user_id,
        referred_date,
        doctor_name,
        reason_for_referral,
        full_name,
        date_of_birth,
        sex,
        contact_number,
        address,
        parent_name,
        parent_dob,
        spouse_name,
        spouse_dob,
        is_philhealth_member,
        philhealth_number,
        blood_pressure,
        weight,
        temperature,
        nurse_notes
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "issssssssssssissdds",
        $user_id,
        $referred_date,
        $doctor_name,
        $reason_for_referral,
        $full_name,
        $date_of_birth,
        $sex,
        $contact_number,
        $address,
        $parent_name,
        $parent_dob,
        $spouse_name,
        $spouse_dob,
        $is_philhealth_member,
        $philhealth_number,
        $blood_pressure,
        $weight,
        $temperature,
        $nurse_notes
    );

    if (mysqli_stmt_execute($stmt)) {

        $referral_id = mysqli_insert_id($conn);

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: generate_referral_pdf.php?id=" . $referral_id);
        exit();

    } else {

        echo "Error saving referral: " . mysqli_stmt_error($stmt);

    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>