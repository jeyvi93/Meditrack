<?php
session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT * FROM users WHERE email = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        // Temporary (plain text password)
        if ($password == $user["password"]) {

            // ==========================
            // STORE USER SESSION
            // ==========================

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];


            // ==========================
            // ROLE-BASED REDIRECTION
            // ==========================

            // CLINIC SIDE
            if (
                $user["role"] == "Admin" ||
                $user["role"] == "Clinic Staff"
            ) {

                header("Location: ../clinic/dashboard.php");
                exit();

            }


            // STUDENT / STAFF SIDE
            elseif (
                $user["role"] == "Student" ||
                $user["role"] == "Teaching Staff" ||
                $user["role"] == "Non-Teaching Staff"
            ) {

                header("Location: ../patient/view_appointments.php");
                exit();

            }


            // UNKNOWN ROLE
            else {

                header("Location: ../login.php?error=invalid_role");
                exit();

            }

        } else {

            // Wrong password
            header("Location: ../login.php?error=1");
            exit();

        }

    } else {

        // Email not found
        header("Location: ../login.php?error=1");
        exit();

    }

} else {

    // Direct access to login_process.php
    header("Location: ../login.php");
    exit();

}
?>