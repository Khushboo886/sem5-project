<?php
require_once '../includes/session.php';
requireEmployee(); // Only employee can access
require_once '../includes/db.php';
include '../includes/header.php';
?>

<div class="cc-app">
    <?php include '../includes/emp_sidebar.php'; ?>

    <main class="cc-admin-main-content" style="flex:1; padding:20px;">
        <h2 style="text-align:center; margin-bottom:20px;">Employee Dashboard</h2>

        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:20px;">
            <div style="background:#0b1220; color:#fff; border-radius:16px; box-shadow:0 4px 8px rgba(0,0,0,0.2); padding:20px; width:280px;">
                <h3>Attendance Summary</h3>
                <p>Total Days: 0</p>
                <p>Present: 0</p>
                <p>Late: 0</p>
                <p>Absent: 0</p>
            </div>

            <div style="background:#0b1220; color:#fff; border-radius:16px; box-shadow:0 4px 8px rgba(0,0,0,0.2); padding:20px; width:280px;">
                <h3>Leave Summary</h3>
                <p>Approved: 0</p>
                <p>Pending: 0</p>
                <p>Rejected: 0</p>
            </div>

            <div style="background:#0b1220; color:#fff; border-radius:16px; box-shadow:0 4px 8px rgba(0,0,0,0.2); padding:20px; width:280px;">
                <h3>Profile</h3>
                <p><strong>Name:</strong> <?= $_SESSION['name'] ?? 'Unknown'; ?></p>
                <p><strong>Email:</strong> <?= $_SESSION['email'] ?? 'Not available'; ?></p>
                <a href="profile.php" style="color:#6c9ef8; text-decoration:none;">View Full Profile →</a>
            </div>
        </div>

        <div style="margin-top:30px; background:#fff; border-radius:12px; padding:20px; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
            <h3>Recent Announcements</h3>
            <p>No announcements yet.</p>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
