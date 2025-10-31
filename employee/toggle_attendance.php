<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once '../includes/session.php';
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success'=>false, 'message'=>'Not logged in']);
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$today_date = date('Y-m-d');
$now_datetime = date('Y-m-d H:i:s');
$current_time = date('H:i:s'); // for comparison

try {
    // Check if already have an attendance record for today
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $today_date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // 🕗 CLOCK-IN
    if (!$row) {
        // Determine remark based on clock-in time
        $remarks = (strtotime($current_time) > strtotime('10:30:00')) 
            ? 'Late arrival' 
            : 'On time';

        $ins = $pdo->prepare("
            INSERT INTO attendance (user_id, date, check_in, status, remarks, created_at)
            VALUES (?, ?, ?, 'present', ?, ?)
        ");
        $ins->execute([$user_id, $today_date, $now_datetime, $remarks, $now_datetime]);

        echo json_encode([
            'success' => true,
            'action' => 'clock_in',
            'server_time' => $now_datetime,
            'message' => 'Clocked in (' . $remarks . ')'
        ]);
        exit;
    }

    // 🕕 CLOCK-OUT
    if ($row && (empty($row['check_out']) || $row['check_out'] === '0000-00-00 00:00:00')) {
        $check_in_time = strtotime($row['check_in']);
        $check_out_time = strtotime($now_datetime);

        $check_out_hour = date('H:i:s', $check_out_time);

        // Determine remark for clock-out
        if (strtotime($check_out_hour) < strtotime('14:30:00')) {
            $remarks = 'Half-day';
            $status = 'half-day';
        } elseif (strtotime($check_out_hour) >= strtotime('19:30:00')) {
            $remarks = 'Full day completed';
            $status = 'present';
        } else {
            $remarks = 'Left early';
            $status = 'half-day';
        }

        $upd = $pdo->prepare("
            UPDATE attendance
            SET check_out = ?, remarks = ?, status = ?, created_at = ?
            WHERE id = ?
        ");
        $upd->execute([$now_datetime, $remarks, $status, $now_datetime, $row['id']]);

        echo json_encode([
            'success' => true,
            'action' => 'clock_out',
            'server_time' => $now_datetime,
            'message' => 'Clocked out (' . $remarks . ')'
        ]);
        exit;
    }

    // 🟡 Already clocked out
    echo json_encode([
        'success' => false,
        'message' => 'Already clocked out for today'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['success'=>false, 'message'=>'DB error: '.$e->getMessage()]);
    exit;
}
?>