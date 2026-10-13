
<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$allowedRoles = ['Student', 'Teaching Staff', 'Non-Teaching Staff'];

if (
    empty($_SESSION['user_id']) ||
    !in_array($_SESSION['role'] ?? '', $allowedRoles, true)
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

$sessionCsrfToken = $_SESSION['csrf_token'] ?? '';
$requestCsrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (
    $sessionCsrfToken === '' ||
    $requestCsrfToken === '' ||
    !hash_equals($sessionCsrfToken, $requestCsrfToken)
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/firebase_messaging.php';

try {
    $userId = (int) $_SESSION['user_id'];

    $stmt = $conn->prepare(
        'SELECT token FROM fcm_tokens WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1'
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No saved notification token.']);
        exit;
    }

    $accessToken = getFirebaseAccessToken();
    $client = new \GuzzleHttp\Client();

    $response = $client->post(
        'https://fcm.googleapis.com/v1/projects/' .
        FIREBASE_PROJECT_ID . '/messages:send',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'message' => [
                    'token' => $row['token'],
                    'data' => [
                        'title' => 'MediTrack Test Notification',
                        'body' => 'Bro, your MediTrack push notifications are working!',
                        'url' => '/patient/view_appointments.php'
                    ]
                ]
            ],
            'timeout' => 15
        ]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Test notification sent to Firebase.'
    ]);

} catch (\GuzzleHttp\Exception\RequestException $e) {
    http_response_code(502);
    error_log('FCM test request failed: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Firebase rejected the request. Check the PHP error log.'
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('FCM test failed: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to send the test notification. Check the PHP error log.'
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
