<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] === 'Clinic Staff') {
    header("Location: ../clinic/staffs.php");
    exit();
}

require_once "../config/database.php";

if (isset($_GET['id'])) {

    $staff_id = $_GET['id'];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM staffs WHERE staff_id_number = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $staff_id);

    if (mysqli_stmt_execute($stmt)) {

        $_SESSION['success'] = "Staff profile deleted successfully.";

    } else {

        $_SESSION['error'] = "Failed to delete staff profile.";

    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

header("Location: ../clinic/staffs.php");
exit();