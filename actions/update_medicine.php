<?php

session_start();
require_once "../config/database.php";
require_once "../includes/clinic_activity_logger.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit();
    
}

if ($_SESSION['role'] === 'Clinic Staff') {
    echo json_encode([
        "success" => false,
        "message" => "You are not authorized to update medicine."
    ]);
    exit();
}

$role = $_SESSION['role'] ?? '';

if (in_array($role, [
    'Student',
    'Teaching Staff',
    'Non-Teaching Staff'
], true)) {

    header("Location: ../patient/view_consultation.php");
    exit();

}

if ($role === 'Administrator') {

    header("Location: ../admin/accounts.php");
    exit();

}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit();
}

$id = (int)($_POST['id'] ?? 0);
$medicine_name = $_POST['medicine_name'] ?? '';
$batch_number = $_POST['batch_number'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 0);
$supplier = $_POST['supplier'] ?? '';
$date_acquired = $_POST['date_acquired'] ?? '';
$expiry_date = $_POST['expiry_date'] ?? '';

if (
    !$id ||
    !$medicine_name ||
    !$batch_number ||
    !isset($_POST['quantity']) ||
    !$supplier ||
    !$date_acquired ||
    !$expiry_date
) {
    echo json_encode([
        "success" => false,
        "message" => "Please complete all fields."
    ]);
    exit();
}

/* Get original medicine details before updating */
$oldSql = "
    SELECT medicine_name, batch_number, quantity,
           supplier, date_acquired, expiry_date
    FROM medicines
    WHERE id = ?
";

$oldStmt = mysqli_prepare($conn, $oldSql);

if (!$oldStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve original medicine information."
    ]);
    exit();
}

mysqli_stmt_bind_param($oldStmt, "i", $id);
mysqli_stmt_execute($oldStmt);

$oldResult = mysqli_stmt_get_result($oldStmt);
$oldMedicine = mysqli_fetch_assoc($oldResult);

mysqli_stmt_close($oldStmt);

if (!$oldMedicine) {
    echo json_encode([
        "success" => false,
        "message" => "Medicine record not found."
    ]);
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE medicines
     SET medicine_name = ?,
         batch_number = ?,
         quantity = ?,
         supplier = ?,
         date_acquired = ?,
         expiry_date = ?
     WHERE id = ?"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => mysqli_error($conn)
    ]);
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "ssisssi",
    $medicine_name,
    $batch_number,
    $quantity,
    $supplier,
    $date_acquired,
    $expiry_date,
    $id
);

if (mysqli_stmt_execute($stmt)) {

    /* Identify the actual fields changed */
    $fieldLabels = [
        'medicine_name' => 'medicine name',
        'batch_number'  => 'batch number',
        'quantity'      => 'quantity',
        'supplier'      => 'supplier',
        'date_acquired' => 'date acquired',
        'expiry_date'   => 'expiry date'
    ];

    $newValues = [
        'medicine_name' => $medicine_name,
        'batch_number'  => $batch_number,
        'quantity'      => $quantity,
        'supplier'      => $supplier,
        'date_acquired' => $date_acquired,
        'expiry_date'   => $expiry_date
    ];

    $changes = [];

    foreach ($fieldLabels as $field => $label) {

        $oldValue = (string) ($oldMedicine[$field] ?? '');
        $newValue = (string) ($newValues[$field] ?? '');

        if ($oldValue !== $newValue) {
            $changes[] = "{$label} {$oldValue} --> {$newValue}";
        }
    }

    /* Record only actual changes */
    if (!empty($changes)) {

        $description = "Updated medicine information for {$medicine_name}. "
                    . "Batch: {$batch_number}. "
                    . "Edited the " . implode('; ', $changes) . ".";

        logClinicActivity(
            $conn,
            'Updated',
            'Medicine',
            $description
        );
    }

    echo json_encode([
        "success" => true,
        "message" => "Medicine updated successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);