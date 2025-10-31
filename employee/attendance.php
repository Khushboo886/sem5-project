<?php
require_once '../includes/session.php';
requireEmployee(); // only employees can access
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
date_default_timezone_set('Asia/Kolkata');
$current_time = date('Y-m-d H:i:s');
$current_date = date('Y-m-d');

// Clock In/Out Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'clock_in') {
        // Prevent duplicate clock-ins for the same day
        $check = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
        $check->execute([$user_id, $current_date]);
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO attendance (user_id, date, check_in, status) VALUES (?, ?, ?, 'present')");
            $stmt->execute([$user_id, $current_date, $current_time]);
        }
    } elseif ($action === 'clock_out') {
        $stmt = $pdo->prepare("SELECT check_in FROM attendance WHERE user_id = ? AND date = ?");
        $stmt->execute([$user_id, $current_date]);
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($attendance) {
            $check_in_time = strtotime($attendance['check_in']);
            $check_out_time = strtotime($current_time);
            $remarks = '-';

            // Remarks Conditions
            $ten_thirty = strtotime("$current_date 10:30:00");
            $two_thirty = strtotime("$current_date 14:30:00");
            $seven_thirty = strtotime("$current_date 19:30:00");

            if ($check_in_time > $ten_thirty) {
                $remarks = 'Late arrival';
            } elseif ($check_out_time < $two_thirty) {
                $remarks = 'Half-day';
            } elseif ($check_out_time >= $seven_thirty) {
                $remarks = 'Full day completed';
            } else {
                $remarks = 'On time';
            }

            $stmt = $pdo->prepare("UPDATE attendance SET check_out = ?, remarks = ? WHERE user_id = ? AND date = ?");
            $stmt->execute([$current_time, $remarks, $user_id, $current_date]);
        }
    }

    header("Location: attendance.php");
    exit;
}

// ✅ Fetch today's record
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id, $current_date]);
$latest = $stmt->fetch(PDO::FETCH_ASSOC);

// Determine if clocked in
$isClockedIn = ($latest && empty($latest['check_out']));

// ✅ Fetch full history
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY date DESC");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ✅ Layout container -->
<div class="cc-app" style="display:flex; min-height:100vh; background:#f5f6fa;">

    <!-- Sidebar -->
    <?php include '../includes/emp_sidebar.php'; ?>

    <!-- Main content -->
    <main class="cc-main" style="flex:1; padding:40px;">

        <!-- Page Title -->
        <h2 style="margin-bottom:25px; font-weight:600; color:#222;">
            Employee Dashboard &gt; Attendance
        </h2>

        <!-- Status + Clock Section -->
        <div style="background:white; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.1); padding:25px; width:400px; margin-bottom:35px;">
            <h3 style="margin-bottom:10px;">Current Status</h3>
            <p style="color:<?= $isClockedIn ? '#28a745' : '#dc3545'; ?>; font-weight:500;">
                <?= $isClockedIn ? '● Clocked In' : '● Clocked Out'; ?>
            </p>

            <form method="POST" style="margin-top:15px;">
                <?php if (!$isClockedIn): ?>
                    <button type="submit" name="action" value="clock_in"
                        style="background:#0d6efd; color:white; border:none; padding:10px 25px; border-radius:6px; cursor:pointer; font-weight:500;">
                        Clock In
                    </button>
                <?php else: ?>
                    <button type="submit" name="action" value="clock_out"
                        style="background:#dc3545; color:white; border:none; padding:10px 25px; border-radius:6px; cursor:pointer; font-weight:500;">
                        Clock Out
                    </button>
                <?php endif; ?>
            </form>

            <p style="margin-top:15px; font-weight:500;">
                Current Time: <span id="current-time"><?= date('h:i:s A'); ?></span>
            </p>
        </div>

        <!-- Attendance History Table -->
        <div style="background:white; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.1); padding:25px;">
            <h3 style="margin-bottom:15px; color:#222;">Attendance History</h3>
            <table style="width:100%; border-collapse:collapse; font-size:15px;">
                <thead>
                    <tr style="background:linear-gradient(180deg,#0b1220,#17202a); color:white;">
                        <th style="padding:10px;">Date</th>
                        <th style="padding:10px;">Clock In</th>
                        <th style="padding:10px;">Clock Out</th>
                        <th style="padding:10px;">Status</th>
                        <th style="padding:10px;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($history) > 0): ?>
                        <?php foreach ($history as $row): ?>
                            <tr style="border-bottom:1px solid #eee; text-align:center;">
                                <td style="padding:10px;"><?= htmlspecialchars($row['date']); ?></td>
                                <td style="padding:10px;"><?= htmlspecialchars($row['check_in']); ?></td>
                                <td style="padding:10px;"><?= $row['check_out'] ? htmlspecialchars($row['check_out']) : '-'; ?></td>
                                <td style="padding:10px; text-transform:capitalize;"><?= htmlspecialchars($row['status']); ?></td>
                                <td style="padding:10px;"><?= htmlspecialchars($row['remarks'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding:10px; text-align:center;">No attendance records yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<script>
    // Live clock
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-IN', { hour12: true });
        document.getElementById('current-time').textContent = timeString;
    }
    setInterval(updateTime, 1000);
</script>

<?php include '../includes/footer.php'; ?>