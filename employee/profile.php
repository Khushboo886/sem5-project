<?php
require_once '../includes/session.php';
requireEmployee(); // only employees can access
require_once '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Fetch user basic info
$stmt = $pdo->prepare("SELECT id, company_id, name, email, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch employee details
$stmt = $pdo->prepare("SELECT * FROM employee_details WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- Layout container -->
<div class="cc-app" style="display:flex; min-height:100vh; background:#f5f7fa;">
    <!-- Sidebar -->
    <?php include '../includes/emp_sidebar.php'; ?>

    <!-- Main content -->
    <main class="cc-main" style="flex:1; padding:30px;">
        <h2 style="margin-bottom:20px;">MY PROFILE</h2>

        <div style="max-width:900px;">

            <!-- Basic Info Card -->
            <div style="
                background:white;
                border-radius:12px;
                padding:24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.08);
                margin-bottom:25px;
            ">
                <h3 style="margin-top:0; margin-bottom:15px; font-weight:600;">Basic Information</h3>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div>
                        <label style="font-weight:600; color:#444;">Full Name</label>
                        <div style="margin-top:6px;"><?= htmlspecialchars($user['name']) ?></div>
                    </div>

                    <div>
                        <label style="font-weight:600; color:#444;">Email</label>
                        <div style="margin-top:6px;"><?= htmlspecialchars($user['email']) ?></div>
                    </div>

                    <div>
                        <label style="font-weight:600; color:#444;">Joined System On</label>
                        <div style="margin-top:6px;"><?= date('d M Y', strtotime($user['created_at'])) ?></div>
                    </div>
                </div>
            </div>

            <!-- Employee Details Card -->
            <div style="
                background:white;
                border-radius:12px;
                padding:24px;
                box-shadow:0 2px 8px rgba(0,0,0,0.08);
            ">
                <h3 style="margin-top:0; margin-bottom:15px; font-weight:600;">Employee Details</h3>

                <?php if (!$details): ?>
                    <p style="color:#666;">Your employer has not added your details yet.</p>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div>
                            <label style="font-weight:600; color:#444;">Employee Name</label>
                            <div style="margin-top:6px;"><?= htmlspecialchars($details['employee_name'] ?? '-') ?></div>
                        </div>

                        <div>
                            <label style="font-weight:600; color:#444;">Employee ID</label>
                            <div style="margin-top:6px;"><?= htmlspecialchars($details['employee_id'] ?? '-') ?></div>
                        </div>

                        <div>
                            <label style="font-weight:600; color:#444;">Department</label>
                            <div style="margin-top:6px;"><?= htmlspecialchars($details['department'] ?? '-') ?></div>
                        </div>

                        <div>
                            <label style="font-weight:600; color:#444;">Position</label>
                            <div style="margin-top:6px;"><?= htmlspecialchars($details['position'] ?? '-') ?></div>
                        </div>

                        <div>
                            <label style="font-weight:600; color:#444;">Joining Date</label>
                            <div style="margin-top:6px;"><?= htmlspecialchars($details['join_date'] ?? '-') ?></div>
                        </div>

                        <div>
                            <label style="font-weight:600; color:#444;">Phone</label>
                            <div style="margin-top:6px;"><?= htmlspecialchars($details['phone'] ?? '-') ?></div>
                        </div>

                        <div style="grid-column:1 / -1;">
                            <label style="font-weight:600; color:#444;">Address</label>
                            <div style="margin-top:6px;"><?= nl2br(htmlspecialchars($details['address'] ?? '-')) ?></div>
                        </div>

                        <div>
                            <label style="font-weight:600; color:#444;">Emergency Contact</label>
                            <div style="margin-top:6px;"><?= htmlspecialchars($details['emergency_contact'] ?? '-') ?></div>
                        </div>

                        <div>
                            <label style="font-weight:600; color:#444;">Emergency Phone</label>
                            <div style="margin-top:6px;"><?= htmlspecialchars($details['emergency_phone'] ?? '-') ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>