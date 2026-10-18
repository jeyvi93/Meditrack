
<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please log in first.'
    ]);
    exit();
}


$sessionCsrfToken = $_SESSION['csrf_token'] ?? '';
$requestCsrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (
    $sessionCsrfToken === '' ||
    $requestCsrfToken === '' ||
    !hash_equals($sessionCsrfToken, $requestCsrfToken)
) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token.'
    ]);
    exit();
}


$allowedRoles = [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff'
];

if (!in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Notifications are not enabled for this account.'
    ]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$token = trim($data['token'] ?? '');
$baseUrl = trim($data['base_url'] ?? '');

if (
    $baseUrl === '' ||
    !filter_var($baseUrl, FILTER_VALIDATE_URL) ||
    !in_array(
        strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)),
        ['http', 'https'],
        true
    )
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid app URL.'
    ]);
    exit();
}

// Keep only the app origin and base path; discard query strings/fragments.
$baseUrl = rtrim($baseUrl, '/') . '/';

if ($token === '' || strlen($token) > 4096) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid notification token.'
    ]);
    exit();
}

require_once '../config/database.php';

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("
    INSERT INTO fcm_tokens (user_id, token, base_url)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
        base_url = VALUES(base_url),
        updated_at = CURRENT_TIMESTAMP
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to prepare token storage.'
    ]);
    exit();
}

$stmt->bind_param('iss', $userId, $token, $baseUrl);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Notification token saved.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to save notification token.'
    ]);
}

$stmt->close();
$conn->close();
?>
