<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'School Nurse') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit();
}

require_once "../config/database.php";

$medicine_name = trim($_GET['medicine_name'] ?? '');

if ($medicine_name === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Medicine name is required.'
    ]);
    exit();
}

$sql = "
    SELECT
        id,
        batch_number,
        quantity,
        low_stock_threshold,
        date_acquired,
        expiry_date,
        supplier
    FROM medicines
    WHERE medicine_name = ?
    AND status = 'Active'
    AND expiry_date >= CURDATE()
    AND is_quarantined = 0
    ORDER BY expiry_date ASC
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve medicine batches.'
    ]);
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $medicine_name);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$batches = [];
$today = strtotime(date('Y-m-d'));
$nearExpiryLimit = strtotime('+30 days');

while ($row = mysqli_fetch_assoc($result)) {

    if (strtotime($row['expiry_date']) <= $nearExpiryLimit) {
        $status = 'Near Expiry';
    } elseif ($row['quantity'] <= $row['low_stock_threshold']) {
        $status = 'Low Stock';
    } else {
        $status = 'In Stock';
    }

    $batches[] = [
        'id' => (int) $row['id'],
        'batch_number' => $row['batch_number'],
        'quantity' => (int) $row['quantity'],
        'date_acquired' => $row['date_acquired'],
        'expiry_date' => $row['expiry_date'],
        'supplier' => $row['supplier'],
        'status' => $status
    ];
}

echo json_encode([
    'success' => true,
    'batches' => $batches
]);

mysqli_stmt_close($stmt);
?>
