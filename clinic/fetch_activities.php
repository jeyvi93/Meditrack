<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit();
}

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['School Nurse', 'Clinic Staff'], true)) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "You are not authorized to view clinic activities."
    ]);

    exit();
}

require_once "../config/database.php";

function renderActivityItem($activity, $isRecent = false)
{
    $type = $activity['activity_type'];
    $action = $activity['action'];

    if ($type === 'Appointment') {
        $iconClass = 'approved';
        $icon = 'bx-calendar';

        if ($action === 'Approved' || $action === 'Completed') {
            $iconClass = 'approved';
            $icon = 'bx-check-circle';
        } elseif ($action === 'Cancelled' || $action === 'Rejected') {
            $iconClass = 'cancel';
            $icon = 'bx-x-circle';
        } elseif ($action === 'Rescheduled') {
            $iconClass = 'pending';
            $icon = 'bx-calendar';
        } elseif ($action === 'Created') {
            $iconClass = 'pending';
            $icon = 'bx-calendar-plus';
        }
    } elseif ($type === 'Medicine') {
        $iconClass = 'medicine';
        $icon = 'bx-capsule';
    } else {
        $iconClass = 'pending';
        $icon = 'bx-info-circle';
    }

    $safeAction = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
    $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
    $safeDescription = htmlspecialchars(
        $activity['description'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );

    if ($isRecent) {
        $safeDescription = nl2br(htmlspecialchars(
            trim($activity['description'] ?? ''),
            ENT_QUOTES,
            'UTF-8'
        ));
    }

    $isMedicineAlert = !empty($activity['is_medicine_alert']);

    $timestamp = $isMedicineAlert
        ? 'Current inventory status'
        : date(
            "M d, Y • h:i A",
            strtotime($activity['created_at'])
        );

    return '
        <div class="activity-item">
            <div class="activity-icon ' . $iconClass . '">
                <i class="bx ' . $icon . '"></i>
            </div>
            <div class="activity-details">
                <h4>' . $safeAction . ' ' . $safeType . '</h4>
                <p>' . $safeDescription . '</p>
                <span>' . $timestamp . '</span>
            </div>
        </div>
    ';
}

$appointmentActivities = [];
$medicineAlerts = [];

// ==========================
// FETCH APPOINTMENT ACTIVITIES
// ==========================

$allActivitySql = "
    SELECT activity_type, action, description, created_at
    FROM activity_logs
    WHERE activity_type = 'Appointment'
    ORDER BY created_at DESC
    LIMIT 50
";

$allActivityResult = mysqli_query($conn, $allActivitySql);

if (!$allActivityResult) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch appointment activities"
    ]);

    exit();
}

while ($row = mysqli_fetch_assoc($allActivityResult)) {
    $appointmentActivities[] = $row;
}


// ==========================
// FETCH MEDICINE STATUS ALERTS
// ==========================

$medicineAlertSql = "
    SELECT
        medicine_name,
        batch_number,
        quantity,
        low_stock_threshold,
        expiry_date,
        CASE
            WHEN expiry_date < CURDATE()
                THEN 'Expired'
            WHEN quantity <= low_stock_threshold
                THEN 'Low Stock'
            ELSE 'Near Expiry'
        END AS alert_status
    FROM medicines
    WHERE
        (
            expiry_date < CURDATE()
            AND status != 'Disposed'
        )
        OR
        (
            status = 'Active'
            AND is_quarantined = 0
            AND expiry_date >= CURDATE()
            AND (
                quantity <= low_stock_threshold
                OR expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            )
        )
";

$medicineAlertResult = mysqli_query($conn, $medicineAlertSql);

if (!$medicineAlertResult) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch medicine alerts"
    ]);

    exit();
}


while ($row = mysqli_fetch_assoc($medicineAlertResult)) {

    $medicineName = $row['medicine_name'];
    $batchNumber = $row['batch_number'];
    $quantity = $row['quantity'];
    $expiryDate = date("M d, Y", strtotime($row['expiry_date']));
    $alertStatus = $row['alert_status'];

    if ($alertStatus === 'Expired') {
        $description = "$medicineName (Batch: $batchNumber) expired on $expiryDate.";
    } elseif ($alertStatus === 'Low Stock') {
        $description = "$medicineName (Batch: $batchNumber) has low stock. Remaining: $quantity";
    } else {
        $description = "$medicineName (Batch: $batchNumber) will expire on $expiryDate.";
    }

    $medicineAlerts[] = [
        'activity_type' => 'Medicine',
        'action' => $alertStatus,
        'description' => $description,
        'is_medicine_alert' => true
    ];
}


// ==========================
// COMBINE ACTIVITIES
// ==========================

$allActivities = [];

$maxItems = max(
    count($medicineAlerts),
    count($appointmentActivities)
);

for ($i = 0; $i < $maxItems; $i++) {

    // Medicine alerts first
    if (isset($medicineAlerts[$i])) {
        $allActivities[] = $medicineAlerts[$i];
    }

    // Appointment actions next
    if (isset($appointmentActivities[$i])) {
        $allActivities[] = $appointmentActivities[$i];
    }
}

$recentActivities = array_slice($allActivities, 0, 5);
$allActivities = array_slice($allActivities, 0, 50);

$recentHtml = '';

foreach ($recentActivities as $activity) {
    $recentHtml .= renderActivityItem($activity, true);
}

$allHtml = '';

foreach ($allActivities as $activity) {
    $allHtml .= renderActivityItem($activity);
}

echo json_encode([
    "success" => true,
    "recent_html" => $recentHtml,
    "all_html" => $allHtml
]);

?>
