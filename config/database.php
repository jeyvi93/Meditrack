
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
        $host = $_ENV['DB_HOST'] ?? (getenv('DB_HOST') ?: '');
        $port = (int) ($_ENV['DB_PORT'] ?? (getenv('DB_PORT') ?: 4000));
        $username = $_ENV['DB_USER'] ?? (getenv('DB_USER') ?: '');
        $password = $_ENV['DB_PASSWORD'] ?? (getenv('DB_PASSWORD') ?: '');
        $database = $_ENV['DB_NAME'] ?? (getenv('DB_NAME') ?: '');

        $caCert = $_ENV['TIDB_CA_CERT_PATH']
            ?? (getenv('TIDB_CA_CERT_PATH') ?: '');

        error_log("TiDB CA source path configured: " . ($caCert !== '' ? 'YES' : 'NO'));
        error_log("TiDB CA file exists: " . (is_file($caCert) ? 'YES' : 'NO'));
        error_log("TiDB CA readable: " . (is_readable($caCert) ? 'YES' : 'NO'));
        error_log("Secret directory exists: " . (is_dir('/etc/secrets') ? 'YES' : 'NO'));

        $missing = [];

        if ($host === '') {
            $missing[] = 'DB_HOST';
        }

        if ($username === '') {
            $missing[] = 'DB_USER';
        }

        if ($password === '') {
            $missing[] = 'DB_PASSWORD';
        }

        if ($database === '') {
            $missing[] = 'DB_NAME';
        }

        if ($caCert === '') {
            $missing[] = 'TIDB_CA_CERT_PATH';
        } elseif (!is_file($caCert)) {
            $missing[] = 'CA_CERT_FILE_NOT_FOUND';
        } elseif (!is_readable($caCert)) {
            $missing[] = 'CA_CERT_FILE_NOT_READABLE';
        }

        if (!empty($missing)) {
            error_log('Database configuration check failed: ' . implode(', ', $missing));
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
