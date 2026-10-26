<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);

    exit();
}

require_once "../config/database.php";

$sql = "
    SELECT
        a.*,
        u.full_name,
        s.student_id_number,
        st.staff_id_number
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.id
    LEFT JOIN students s ON u.student_id = s.id
    LEFT JOIN staffs st ON u.staff_id = st.id
    WHERE a.status = 'Pending'
    ORDER BY a.appointment_date, a.appointment_time
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch pending appointments'
    ]);

    exit();
}

$count = mysqli_num_rows($result);

ob_start();

if ($count > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $patientId = !empty($row['student_id_number'])
            ? $row['student_id_number']
            : ($row['staff_id_number'] ?? '');

        $fullName = $row['full_name'] ?? '';
        $appointmentId = (int) $row['id'];
        $purpose = $row['purpose'] ?? '';

        $safePatientId = htmlspecialchars(
            $patientId,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeName = htmlspecialchars(
            $fullName,
            ENT_QUOTES,
            'UTF-8'
        );

        $safePurpose = htmlspecialchars(
            $purpose,
            ENT_QUOTES,
            'UTF-8'
        );

        // Safely encode the name for the existing Reject modal.
        $jsName = json_encode(
            $fullName,
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_TAG |
            JSON_HEX_AMP
        );
        ?>

        <tr>
            <td>
                <strong><?= $safePatientId ?></strong>
            </td>

            <td><?= $safeName ?></td>

            <td>
                <?= date(
                    "M d, Y",
                    strtotime($row['appointment_date'])
                ) ?>
            </td>

            <td>
                <?= date(
                    "h:i A",
                    strtotime($row['appointment_time'])
                ) ?>
            </td>

            <td><?= $safePurpose ?></td>

            <td>
                <form
                    action="../actions/approve_appointment.php"
                    method="POST"
                    style="display:inline;"
                >
                    <input
                        type="hidden"
                        name="appointment_id"
                        value="<?= $appointmentId ?>"
                    >

                    <button
                        type="submit"
                        class="btn-icon icon-approve"
                        title="Approve"
                    >
                        <i class="fas fa-check"></i>
                    </button>
                </form>

                <button
                    type="button"
                    class="btn-icon icon-reject"
                    title="Reject"
                    onclick='openRejectModal(<?= htmlspecialchars(
                        $jsName,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>, <?= $appointmentId ?>)'
                >
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>

        <?php
    }
} else {
    ?>
    <tr>
        <td colspan="6" style="text-align:center;">
            No pending appointments found.
        </td>
    </tr>
    <?php
}

$html = ob_get_clean();

mysqli_free_result($result);
mysqli_close($conn);

echo json_encode([
    'success' => true,
    'count' => $count,
    'html' => $html
]);
