<?php
require_once '../includes/session.php';
requireEmployee();
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
date_default_timezone_set('Asia/Kolkata');

$currentDate = date('Y-m-d');
$currentTime = date('Y-m-d H:i:s');

/* ===============================
   CLOCK IN / CLOCK OUT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'clock_in') {
        $check = $db->prepare("SELECT id FROM attendance WHERE user_id=? AND date=?");
        $check->execute([$user_id, $currentDate]);

        if (!$check->fetch()) {
            $db->prepare(
                "INSERT INTO attendance (user_id, date, check_in, status)
                 VALUES (?, ?, ?, 'present')"
            )->execute([$user_id, $currentDate, $currentTime]);
        }
    }

    if ($action === 'clock_out') {
        $stmt = $db->prepare(
            "SELECT check_in FROM attendance WHERE user_id=? AND date=?"
        );
        $stmt->execute([$user_id, $currentDate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $checkIn = strtotime($row['check_in']);
            $checkOut = strtotime($currentTime);

            $status = 'present';
            $remarks = 'On time';

            if ($checkIn > strtotime("$currentDate 10:30:00")) {
                $status = 'late';
                $remarks = 'Late arrival';
            }
            if ($checkOut < strtotime("$currentDate 14:30:00")) {
                $status = 'half-day';
                $remarks = 'Left early';
            }

            $db->prepare(
                "UPDATE attendance
                 SET check_out=?, status=?, remarks=?
                 WHERE user_id=? AND date=?"
            )->execute([$currentTime, $status, $remarks, $user_id, $currentDate]);
        }
    }

    header("Location: attendance.php");
    exit;
}

/* ===============================
   FETCH TODAY STATUS
================================ */
$stmt = $db->prepare(
    "SELECT * FROM attendance WHERE user_id=? AND date=?"
);
$stmt->execute([$user_id, $currentDate]);
$today = $stmt->fetch(PDO::FETCH_ASSOC);

$isClockedIn = ($today && !$today['check_out']);

/* ===============================
   FETCH HISTORY
================================ */
$stmt = $db->prepare(
    "SELECT * FROM attendance WHERE user_id=? ORDER BY date DESC"
);
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="cc-app">
    <?php include '../includes/emp_sidebar.php'; ?>

    <main class="cc-main-content">

        <!-- Page Header -->
        <div class="page-header">
            <h1>Attendance</h1>
            <p class="muted">Manage your daily attendance</p>
        </div>

        <!-- Clock Card -->
        <div class="card clock-card">
            <h3>Today’s Status</h3>

            <p class="status <?= $isClockedIn ? 'green' : 'red' ?>">
                ● <?= $isClockedIn ? 'Clocked In' : 'Clocked Out' ?>
            </p>

            <form method="post">
                <?php if (!$isClockedIn): ?>
                    <button name="action" value="clock_in" class="btn primary">
                        Clock In
                    </button>
                <?php else: ?>
                    <button name="action" value="clock_out" class="btn danger">
                        Clock Out
                    </button>
                <?php endif; ?>
            </form>

            <p class="time">
                Current Time: <span id="liveTime"></span>
            </p>
        </div>

        <!-- Attendance History -->
        <div class="card">
            <h3>Attendance History</h3>

            <table class="cc-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history): foreach ($history as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= date('h:i A', strtotime($row['check_in'])) ?></td>
                            <td>
                                <?= $row['check_out']
                                    ? date('h:i A', strtotime($row['check_out']))
                                    : '-' ?>
                            </td>
                            <td>
                                <span class="badge <?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['remarks'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5" class="no-data">
                                No attendance records yet
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<!-- ===============================
     PAGE-ONLY STYLES
================================ -->
<style>
.cc-main-content{
  margin-left: 300px;
  padding: 32px;
  min-height: calc(100vh - 120px);
}

.page-header{
  margin-bottom: 24px;
}

.page-header h1{
  margin: 0;
  font-size: 28px;
}

.muted{ color: var(--muted); }

.card{
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 28px;
  box-shadow: 0 12px 30px rgba(2,6,23,.25);
}

.clock-card{
  max-width: 420px;
}

.status{
  font-weight: 600;
  margin: 10px 0 16px;
}
.status.green{ color: #2ecc71; }
.status.red{ color: #ff6b6b; }

.time{
  margin-top: 14px;
  font-weight: 500;
}

.btn{
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  font-weight: 600;
  cursor: pointer;
}
.btn.primary{
  background: var(--accent);
  color: #fff;
}
.btn.danger{
  background: #ff5b5b;
  color: #fff;
}

.cc-table{
  width: 100%;
  border-collapse: collapse;
}
.cc-table th{
  background: linear-gradient(180deg,#0b1220,#17202a);
  color: #e6eef8;
  padding: 12px;
  text-align: left;
}
.cc-table td{
  padding: 12px;
  border-bottom: 1px solid var(--border);
}

.badge{
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}
.badge.present{ background:#2ecc71; color:#fff; }
.badge.late{ background:#f1c40f; color:#000; }
.badge.half-day{ background:#e67e22; color:#fff; }
.badge.absent{ background:#ff6b6b; color:#fff; }

.no-data{
  text-align: center;
  padding: 20px;
  color: var(--muted);
}
</style>

<script>
function updateClock() {
    document.getElementById('liveTime').innerText =
        new Date().toLocaleTimeString('en-IN', { hour12: true });
}
setInterval(updateClock, 1000);
updateClock();
</script>

<?php include '../includes/footer.php'; ?>
