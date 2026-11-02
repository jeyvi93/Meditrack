
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

function sendMediTrackEmail(
    string $recipientEmail,
    string $recipientName,
    string $subject,
    string $htmlBody
): bool {

    $mailConfig = require __DIR__ . '/../config/mail.php';

    $appEnv = $_ENV['APP_ENV'] ?? 'development';

    // Use Brevo API on Render
    if ($appEnv === 'production') {

        $apiKey = $_ENV['BREVO_API_KEY'] ?? '';

        if ($apiKey === '') {
            throw new RuntimeException('Brevo API key is missing.');
        }

        $payload = [
            'sender' => [
                'name' => 'MediTrack',
                'email' => $mailConfig['username']
            ],
            'to' => [
                [
                    'email' => $recipientEmail,
                    'name' => $recipientName
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlBody
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'api-key: ' . $apiKey,
                'content-type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($response === false || $httpCode !== 201) {

            error_log(
                "MediTrack Brevo error. HTTP: {$httpCode}. " .
                "cURL: {$curlError}. Response: {$response}"
            );

            throw new RuntimeException(
                'Unable to send email through Brevo.'
            );
        }

        return true;
    }

    // Use Gmail SMTP locally
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $mailConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mailConfig['username'];
    $mail->Password = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $mailConfig['port'];
    $mail->Timeout = 15;

    $mail->setFrom($mailConfig['username'], 'MediTrack');
    $mail->addAddress($recipientEmail, $recipientName);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $htmlBody;

    return $mail->send();
}
