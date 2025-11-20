<?php
require_once '../includes/session.php';
requireEmployee(); // only employees can access
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
date_default_timezone_set('Asia/Kolkata');

// Handle Leave Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = trim($_POST['reason']);

    if (!empty($leave_type) && !empty($start_date) && !empty($end_date) && !empty($reason)) {
        $stmt = $pdo->prepare("
            INSERT INTO leaves (user_id, leave_type, start_date, end_date, reason, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $leave_type, $start_date, $end_date, $reason]);
        header("Location: leave.php");
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

<!-- ✅ Layout container -->
<div class="cc-app" style="display:flex; min-height:100vh; background:#f9fafc;">

    <!-- Sidebar -->
    <?php include '../includes/emp_sidebar.php'; ?>

    <!-- Main content -->
    <main class="cc-main" style="flex:1; padding:30px;">
        <h2 style="margin-bottom:20px;">Leave Management</h2>

        <!-- Apply Leave Card -->
        <div style="background:white; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.1); padding:25px; width:500px; margin-bottom:30px;">
            <h3 style="margin-bottom:15px;">Apply for Leave</h3>

            <?php if (!empty($error)): ?>
                <p style="color:#dc3545; font-weight:500;"><?= htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom:15px;">
                    <label style="font-weight:500;">Leave Type:</label><br>
                    <select name="leave_type" required
                            style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                        <option value="">-- Select Type --</option>
                        <option value="sick">Sick Leave</option>
                        <option value="casual">Casual Leave</option>
                        <option value="vacation">Vacation Leave</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:500;">Start Date:</label><br>
                    <input type="date" name="start_date" required
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:500;">End Date:</label><br>
                    <input type="date" name="end_date" required
                           style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:500;">Reason:</label><br>
                    <textarea name="reason" rows="3" required
                              style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"></textarea>
                </div>

                <button type="submit"
                        style="background:#0d6efd; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer;">
                    Submit Request
                </button>
            </form>
        </div>

        <!-- Leave History Table -->
        <div style="background:white; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.1); padding:25px;">
            <h3 style="margin-bottom:15px;">My Leave Requests</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:linear-gradient(180deg,#0b1220,#17202a); color:white;">
                        <th style="padding:10px;">Leave Type</th>
                        <th style="padding:10px;">Date Range</th>
                        <th style="padding:10px;">Reason</th>
                        <th style="padding:10px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($leaves) > 0): ?>
                        <?php foreach ($leaves as $leave): ?>
                            <tr style="border-bottom:1px solid #ddd;">
                                <td style="padding:10px; text-transform:capitalize;">
                                    <?= htmlspecialchars($leave['leave_type']); ?>
                                </td>
                                <td style="padding:10px;">
                                    <?= htmlspecialchars($leave['start_date']); ?> → <?= htmlspecialchars($leave['end_date']); ?>
                                </td>
                                <td style="padding:10px;"><?= htmlspecialchars($leave['reason']); ?></td>
                                <td style="padding:10px; font-weight:500;
                                    color:<?= $leave['status'] === 'approved' ? '#28a745' : ($leave['status'] === 'rejected' ? '#dc3545' : '#ffc107'); ?>;">
                                    <?= ucfirst(htmlspecialchars($leave['status'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="padding:10px; text-align:center;">No leave requests yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
