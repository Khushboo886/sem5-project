<?php
require_once '../includes/session.php';  // include session helper
requireAdmin();  // only admins can enter this page
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/admin_sidebar.php'; ?>

<div class="cc-main"> 
    <h1 class="admin-dashboard-title">ADMIN DASHBOARD OVERVIEW</h1> 
    
    <div class="cc-stats-grid">
        <div class="cc-card">
            <div class="cc-card-body">
                <div class="cc-card-left">
                    <div class="cc-card-title">Total Employees</div>
                </div>
                <div class="cc-card-value">1</div> 
            </div>
        </div>
        <div class="cc-card">
            <div class="cc-card-body">
                <div class="cc-card-left">
                    <div class="cc-card-title">Active Attendance</div>
                </div>
                <div class="cc-card-value">0</div>
            </div>
        </div>
        <div class="cc-card">
            <div class="cc-card-body">
                <div class="cc-card-left">
                    <div class="cc-card-title">Pending Leave Req.</div>
                </div>
                <div class="cc-card-value">0</div>
            </div>
        </div>
        <div class="cc-card">
            <div class="cc-card-body">
                <div class="cc-card-left">
                    <div class="cc-card-title">Documents Shared</div>
                </div>
                <div class="cc-card-value">0</div> 
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>