
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from the project root
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Detect Render environment
$isProduction = ($_ENV['APP_ENV'] ?? getenv('APP_ENV')) === 'production';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    if ($isProduction) {

        // TiDB Cloud credentials from Render environment variables
        $host = $_ENV['DB_HOST'] ?? '';
        $port = (int) ($_ENV['DB_PORT'] ?? 4000);
        $username = $_ENV['DB_USER'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $database = $_ENV['DB_NAME'] ?? '';

        // Render Secret File containing the TiDB CA certificate
        $caCert = $_ENV['TIDB_CA_CERT_PATH'] ?? getenv('TIDB_CA_CERT_PATH') ?: '';

        if (
            $host === '' ||
            $username === '' ||
            $password === '' ||
            $database === '' ||
            $caCert === '' ||
            !is_readable($caCert)
        ) {
            throw new RuntimeException("Database environment configuration is incomplete.");
        }

        $conn = mysqli_init();

        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);
        if (!$conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true)) {
            throw new RuntimeException("Unable to enable SSL certificate verification.");
        }

        $conn->ssl_set(null, null, $caCert, null, null);

        $conn->real_connect(
            $host,
            $username,
            $password,
            $database,
            $port,
            null,
            MYSQLI_CLIENT_SSL
        );

    } else {

        // Local XAMPP database
        $host = "localhost";
        $port = 3306;
        $username = "root";
        $password = "";
        $database = "meditrack_db";

        $conn = mysqli_connect(
            $host,
            $username,
            $password,
            $database,
            $port
        );
    }

    mysqli_set_charset($conn, "utf8mb4");

} catch (Throwable $e) {

    error_log("Database connection failed: " . $e->getMessage());

    die("Database connection failed. Please try again later.");

}
