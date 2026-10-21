<?php

$status = $_GET["status"] ?? "error";

if ($status === "success") {

    $title = "Email Verified Successfully";
    $message = "Your email address has been verified. You can now log in to your MediTrack account.";

} elseif ($status === "email_changed") {

    $title = "Email Address Updated Successfully";
    $message = "Your new email address has been verified and updated. You can now use it to log in to your MediTrack account.";

} elseif ($status === "expired") {

    $title = "Verification Link Expired";
    $message = "This verification link has expired. Please request a new verification email to verify your account.";

} else {

    $title = "Invalid Verification Link";
    $message = "This verification link is invalid or can no longer be used. Please request a new verification email.";

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

    <!-- Verification Result Styles -->
    <link
        rel="stylesheet"
        href="../assets/css/verification_result.css"
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


    <main class="verification-page">

        <div class="verification-card">


            <!-- LOGO -->

            <div class="verification-logo">

                <img
                    src="../assets/images/med.png"
                    alt="MediTrack Logo"
                >

            </div>


            <!-- STATUS ICON -->

            <?php if ($status === "success" || $status === "email_changed"): ?>

                <div class="verification-icon success">

                    <i class="bx bx-check-circle"></i>

                </div>


            <?php elseif ($status === "expired"): ?>

                <div class="verification-icon expired">

                    <i class="bx bx-time-five"></i>

                </div>


            <?php else: ?>

                <div class="verification-icon error">

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
                href="../login.php"
                class="verification-button"
            >

                <?php
                echo $status === "success"
                    ? "Go to Login"
                    : "Return to Login";
                ?>

            </a>


            <!-- FOOTER -->

            <div class="verification-footer">

                © 2026 MediTrack

            </div>


        </div>

    </main>

</body>

</html>