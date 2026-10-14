    <?php

    require_once "../../config/database.php";

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: ../../login.php");
        exit();
    }

    $token = trim($_POST["token"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Check Required Fields
    |--------------------------------------------------------------------------
    */

    if (empty($token) || empty($password) || empty($confirm_password)) {
        header(
            "Location: ../../patient/reset_password.php?token=" .
            urlencode($token) .
            "&error=required_fields"
        );
        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | Check Password Match
    |--------------------------------------------------------------------------
    */

    if ($password !== $confirm_password) {
        header(
            "Location: ../../patient/reset_password.php?token=" .
            urlencode($token) .
            "&error=password_mismatch"
        );
        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | Password Length
    |--------------------------------------------------------------------------
    */

    if (strlen($password) < 8) {
        header(
            "Location: ../../patient/reset_password.php?token=" .
            urlencode($token) .
            "&error=password_length"
        );
        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | Find Reset Token
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT id, reset_expires
            FROM users
            WHERE reset_token = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) !== 1) {

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header(
            "Location: ../../patient/reset_error.php?error=invalid"
        );
        exit();
    }

    $user = mysqli_fetch_assoc($result);


    /*
    |--------------------------------------------------------------------------
    | Check Expiration
    |--------------------------------------------------------------------------
    */

    if (
        empty($user["reset_expires"]) ||
        strtotime($user["reset_expires"]) < time()
    ) {

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header(
            "Location: ../../patient/reset_error.php?error=expired"
        );
        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | Hash New Password
    |--------------------------------------------------------------------------
    */

    $hashed_password = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    $sql = "UPDATE users
            SET password = ?,
                reset_token = NULL,
                reset_expires = NULL
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $hashed_password,
        $user["id"]
    );

    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header(
            "Location: ../../patient/reset_password.php?token=" .
            urlencode($token) .
            "&error=update_failed"
        );
        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | Return to Login
    |--------------------------------------------------------------------------
    */

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../../login.php?reset=success");
    exit();

    ?>