<?php
require_once '../includes/session.php';
requireEmployee();
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Fetch user basic info
$stmt = $pdo->prepare("
    SELECT id, company_id, name, email, created_at 
    FROM users 
    WHERE id = ? 
    LIMIT 1
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch employee details
$stmt = $pdo->prepare("
    SELECT * 
    FROM employee_details 
    WHERE user_id = ? 
    LIMIT 1
");
$stmt->execute([$user_id]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include '../includes/emp_sidebar.php'; ?>

<main class="cc-main">

    <!-- Page Header -->
    <div class="cc-page-header">
        <div>
            <h1>My Profile</h1>
            <p class="muted">Personal & employment information</p>
        </div>
    </div>

    <div style="max-width:1000px;">

        <!-- Basic Information -->
        <div class="cc-card">
            <h3 style="margin-bottom:18px;">Basic Information</h3>

            <div class="cc-grid-2">
                <div>
                    <label class="cc-label">Full Name</label>
                    <div class="cc-value"><?= htmlspecialchars($user['name']) ?></div>
                </div>

                <div>
                    <label class="cc-label">Email</label>
                    <div class="cc-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>

                <div>
                    <label class="cc-label">Joined On</label>
                    <div class="cc-value">
                        <?= date('d M Y', strtotime($user['created_at'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Details -->
        <div class="cc-card">

            <h3 style="margin-bottom:18px;">Employee Details</h3>

            <?php if (!$details): ?>
                <p class="muted">
                    Your employer has not added your employee details yet.
                </p>
            <?php else: ?>

                <div class="cc-grid-2">
                    <div>
                        <label class="cc-label">Employee Name</label>
                        <div class="cc-value"><?= htmlspecialchars($details['employee_name'] ?? '-') ?></div>
                    </div>

                    <div>
                        <label class="cc-label">Employee ID</label>
                        <div class="cc-value"><?= htmlspecialchars($details['employee_id'] ?? '-') ?></div>
                    </div>

                    <div>
                        <label class="cc-label">Department</label>
                        <div class="cc-value"><?= htmlspecialchars($details['department'] ?? '-') ?></div>
                    </div>

                    <div>
                        <label class="cc-label">Position</label>
                        <div class="cc-value"><?= htmlspecialchars($details['position'] ?? '-') ?></div>
                    </div>

                    <div>
                        <label class="cc-label">Joining Date</label>
                        <div class="cc-value"><?= htmlspecialchars($details['join_date'] ?? '-') ?></div>
                    </div>

                    <div>
                        <label class="cc-label">Phone</label>
                        <div class="cc-value"><?= htmlspecialchars($details['phone'] ?? '-') ?></div>
                    </div>

                    <div style="grid-column:1 / -1;">
                        <label class="cc-label">Address</label>
                        <div class="cc-value">
                            <?= nl2br(htmlspecialchars($details['address'] ?? '-')) ?>
                        </div>
                    </div>

                    <div>
                        <label class="cc-label">Emergency Contact</label>
                        <div class="cc-value"><?= htmlspecialchars($details['emergency_contact'] ?? '-') ?></div>
                    </div>

                    <div>
                        <label class="cc-label">Emergency Phone</label>
                        <div class="cc-value"><?= htmlspecialchars($details['emergency_phone'] ?? '-') ?></div>
                    </div>
                </div>

            <?php endif; ?>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>

<style>
/* ===============================
   CloudConnect Profile Styling
================================ */

.cc-main{
  margin-left:300px;
  padding:32px;
  padding-top:96px;
  min-height:calc(100vh - 140px);
}

.cc-grid-2{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:22px;
}

.cc-label{
  font-weight:600;
  font-size:14px;
  color:var(--muted);
}

.cc-value{
  margin-top:6px;
  font-size:15px;
  color:var(--text);
}

/* Footer fix */
.cc-footer{
  margin-left:300px;
}

/* Responsive */
@media(max-width:1000px){
  .cc-main{
    margin-left:0;
    padding-top:88px;
  }
  .cc-footer{
    margin-left:0;
  }
}
</style>