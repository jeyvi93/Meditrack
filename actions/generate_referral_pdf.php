<?php

session_start();

require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Unauthorized");
}

if (!isset($_GET['id'])) {
    exit("Referral ID is missing.");
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM referrals WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit("Referral not found.");
}

$referral = $result->fetch_assoc();

$stmt->close();

ob_start();

include "../templates/referral_slip.php";

$html = ob_get_clean();

$options = new Options();

$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper([0, 0, 612, 396], 'portrait');

$dompdf->render();

$dompdf->stream(
    "referral_" . $referral['id'] . ".pdf",
    [
        "Attachment" => false
    ]
);