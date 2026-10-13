
<?php

// Firebase Cloud Messaging configuration.
// Keep the service-account JSON outside htdocs.

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;

const FIREBASE_PROJECT_ID = 'meditrack-clinic-2f6a0';

function getFirebaseAccessToken(): string
{
    $keyFile = 'C:/xampp/private/firebase-service-account.json';

    if (!is_readable($keyFile)) {
        throw new RuntimeException(
            'Firebase service-account file is missing or unreadable.'
        );
    }

    $credentials = new ServiceAccountCredentials(
        ['https://www.googleapis.com/auth/firebase.messaging'],
        $keyFile
    );

    $token = $credentials->fetchAuthToken();

    if (empty($token['access_token'])) {
        throw new RuntimeException(
            'Unable to obtain Firebase access token.'
        );
    }

    return $token['access_token'];
}

function sendFirebasePushToUser(
    mysqli $conn,
    int $userId,
    string $title,
    string $body
): void {
    try {
        $stmt = $conn->prepare(
            "SELECT token FROM fcm_tokens WHERE user_id = ?"
        );

        if (!$stmt) {
            throw new RuntimeException(
                "Unable to prepare FCM token query."
            );
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $tokens = [];

        while ($row = $result->fetch_assoc()) {
            $tokens[] = $row['token'];
        }

        $stmt->close();

        if (empty($tokens)) {
            return;
        }

        $scheme = (
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
        ) ? 'https' : 'http';

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $basePath = rtrim(
            dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')),
            '/'
        );

        $appointmentsUrl = $scheme . '://' . $host .
            $basePath . '/patient/view_appointments.php';

        $accessToken = getFirebaseAccessToken();
        $client = new \GuzzleHttp\Client();

        foreach ($tokens as $token) {
            try {
                $client->post(
                    'https://fcm.googleapis.com/v1/projects/' .
                    FIREBASE_PROJECT_ID . '/messages:send',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'message' => [
                                'token' => $token,
                                'data' => [
                                    'title' => $title,
                                    'body' => $body,
                                    'url' => $appointmentsUrl,
                                ],
                            ],
                        ],
                        'timeout' => 15,
                    ]
                );
            } catch (\Throwable $e) {
                error_log(
                    'MediTrack FCM delivery failed: ' .
                    $e->getMessage()
                );
            }
        }
    } catch (\Throwable $e) {
        // Push failures must not undo an approved appointment.
        error_log(
            'MediTrack push setup failed: ' . $e->getMessage()
        );
    }
}
