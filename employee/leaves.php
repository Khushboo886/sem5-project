<?php
require_once '../includes/session.php';
requireEmployee();
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
date_default_timezone_set('Asia/Kolkata');

// Handle Leave Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = $_POST['leave_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';
    $reason     = trim($_POST['reason'] ?? '');

    if ($leave_type && $start_date && $end_date && $reason) {
        $stmt = $pdo->prepare("
            INSERT INTO leaves (user_id, leave_type, start_date, end_date, reason, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $leave_type, $start_date, $end_date, $reason]);
        header("Location: leaves.php");
        exit;
    } else {
        $error = "⚠️ Please fill all fields before submitting.";
    }
}

// Fetch Leave History
$stmt = $pdo->prepare("SELECT * FROM leaves WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/emp_sidebar.php'; ?>

<main class="cc-main">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-title">
            <h1>Leave Management</h1>
            <p>Apply and track your leave requests</p>
        </div>
    </div>

    <!-- APPLY LEAVE -->
    <div class="cc-card" style="max-width:520px; margin-bottom:32px;">
        <h3>Apply for Leave</h3>

        <?php if (!empty($error)): ?>
            <p style="color:#dc3545; font-weight:600; margin-bottom:12px;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Leave Type</label>
                <select name="leave_type" required>
                    <option value="">-- Select Type --</option>
                    <option value="sick">Sick Leave</option>
                    <option value="casual">Casual Leave</option>
                    <option value="vacation">Vacation Leave</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" required>
            </div>

            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" required>
            </div>

            <div class="form-group">
                <label>Reason</label>
                <textarea name="reason" rows="3" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary leave-submit-btn">
    Submit Request
</button>

        </form>
    </div>

    <!-- LEAVE HISTORY -->
<div class="cc-card">
    <h3>My Leave Requests</h3>

    <table class="cc-table">
        <thead>
            <tr>
                <th>Leave Type</th>
                <th>Date Range</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($leaves): ?>
            <?php foreach ($leaves as $leave): ?>
                <tr>
                    <td><?= ucfirst(htmlspecialchars($leave['leave_type'])) ?></td>
                    <td>
                        <?= htmlspecialchars($leave['start_date']) ?>
                        →
                        <?= htmlspecialchars($leave['end_date']) ?>
                    </td>
                    <td><?= htmlspecialchars($leave['reason']) ?></td>
                    <td>
                        <span class="badge badge-<?= htmlspecialchars($leave['status']) ?>">
                            <?= ucfirst(htmlspecialchars($leave['status'])) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="no-data">
                    No leave requests yet.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


</main>

<style>
/* ===============================
   CloudConnect Layout Fix
================================ */

.cc-main{
  margin-left:300px;
  padding:32px;
  padding-top:96px; /* header height */
  min-height:100vh;
}

.page-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  margin-bottom:28px;
}

.page-title h1{
  font-size:26px;
  font-weight:700;
  margin-bottom:6px;
}

.page-title p{
  color:var(--muted);
}

.form-group{
  margin-bottom:14px;
}

.form-group label{
  display:block;
  font-weight:600;
  margin-bottom:6px;
}

.form-group input,
.form-group select,
.form-group textarea{
  width:100%;
  padding:10px;
  border-radius:8px;
  border:1px solid rgba(0,0,0,0.15);
}
/* Leave table polish */
.cc-table{
  width:100%;
  border-collapse:collapse;
  margin-top:12px;
}

.cc-table thead th{
  background:linear-gradient(180deg,#0b1220,#17202a);
  color:#fff;
  padding:12px;
  text-align:left;
  font-weight:600;
}

.cc-table tbody td{
  padding:12px;
  border-bottom:1px solid rgba(0,0,0,0.08);
}

.cc-table tbody tr:hover{
  background:rgba(0,0,0,0.03);
}

.no-data{
  text-align:center;
  padding:18px;
  font-style:italic;
  color:var(--muted);
}
/* Submit Leave Button */
.leave-submit-btn{
  padding:12px 26px;
  border-radius:12px;
  font-weight:600;
  font-size:15px;
  background:linear-gradient(135deg,#4f7cff,#6a5cff);
  color:#fff;
  border:none;
  cursor:pointer;
  transition:all .25s ease;
  box-shadow:0 8px 18px rgba(79,124,255,.35);
}

.leave-submit-btn:hover{
  transform:translateY(-2px);
  box-shadow:0 12px 24px rgba(79,124,255,.45);
}

.leave-submit-btn:active{
  transform:translateY(0);
  box-shadow:0 6px 14px rgba(79,124,255,.3);
}

@media(max-width:1000px){
  .cc-main{
    margin-left:0;
    padding:20px;
    padding-top:96px;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
