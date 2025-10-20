<?php
require_once '../includes/session.php';
requireEmployee();  // only employees can enter
?>

<?php include '../includes/header.php'; ?>
<h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
<p>This is your employee dashboard.</p>
<?php include '../includes/footer.php'; ?>
