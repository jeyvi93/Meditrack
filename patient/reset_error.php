<?php

$error = $_GET["error"] ?? "invalid";

if ($error === "expired") {

    $title = "Reset Link Expired";
    $message = "This password reset link has expired. Please request a new reset link to continue.";

} else {

    $title = "Invalid Reset Link";
    $message = "This password reset link is invalid or can no longer be used. Please request a new reset link.";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($title); ?> | MediTrack
    </title>

    <!-- Shared Styles -->
    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <!-- Reset Error Styles -->
    <link
        rel="stylesheet"
        href="../assets/css/reset_error.css"
    >

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
        rel="stylesheet"
    >

    <!-- Icons -->
    <link
        href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
        rel="stylesheet"
    >

</head>

<body>

    <div class="bg-gradient"></div>


    <main class="reset-page">

        <div class="reset-card">


            <!-- LOGO -->

            <div class="reset-logo">

                <img
                    src="../assets/images/med.png"
                    alt="MediTrack Logo"
                >

            </div>


            <!-- STATUS ICON -->

            <?php if ($error === "expired"): ?>

                <div class="reset-icon expired">

                    <i class="bx bx-time-five"></i>

                </div>

            <?php else: ?>

                <div class="reset-icon error">

                    <i class="bx bx-error-circle"></i>

                </div>

            <?php endif; ?>


            <!-- TITLE -->

            <h1>
                <?php echo htmlspecialchars($title); ?>
            </h1>


            <!-- MESSAGE -->

            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>


            <!-- BUTTON -->

            <a
                href="forgot_password.php"
                class="reset-button"
            >
                Request New Reset Link
            </a>


            <!-- BACK TO LOGIN -->

            <a
                href="../login.php"
                class="login-link"
            >
                Return to Login
            </a>


            <!-- FOOTER -->

            <div class="reset-footer">

                © 2026 MediTrack

            </div>


        </div>

    </main>

</body>

</html>