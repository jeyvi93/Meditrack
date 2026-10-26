
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

    $timestamp = date(
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

$recentActivities = [];
$allActivities = [];

$recentActivitySql = "
    SELECT activity_type, action, description, created_at
    FROM activity_logs
    ORDER BY created_at DESC
    LIMIT 5
";

$recentActivityResult = mysqli_query($conn, $recentActivitySql);

if (!$recentActivityResult) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch recent activities"
    ]);

    exit();
}

while ($row = mysqli_fetch_assoc($recentActivityResult)) {
    $recentActivities[] = $row;
}

$allActivitySql = "
    SELECT activity_type, action, description, created_at
    FROM activity_logs
    ORDER BY created_at DESC
    LIMIT 50
";

$allActivityResult = mysqli_query($conn, $allActivitySql);

if (!$allActivityResult) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch all activities"
    ]);

    exit();
}

while ($row = mysqli_fetch_assoc($allActivityResult)) {
    $allActivities[] = $row;
}

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
