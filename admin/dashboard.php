<?php
require_once '../includes/session.php';  // include session helper
requireAdmin();  // only admins can enter this page
?>

<?php include '../includes/header.php'; ?>
<!-- Sidebar + content layout -->
<div class="cc-app">
	<!-- Hamburger / Sidebar toggle -->
	<button id="cc-hamburger" class="cc-hamburger" aria-label="Toggle navigation" aria-expanded="false">
		<span class="cc-hamburger-box">
			<span class="cc-hamburger-inner"></span>
		</span>
	</button>

	<aside id="cc-sidebar" class="cc-sidebar" aria-hidden="true">
		<div class="cc-sidebar-header">
			<h3>Admin</h3>
		</div>
		<nav class="cc-nav">
			<ul>
				<li><a href="dashboard.php">Dashboard</a></li>
				<li><a href="add_employee.php">Employees</a></li>
				<li><a href="../attendance.php">Attendance</a></li>
				<li><a href="../leave.php">Leaves</a></li>
				<li><a href="../accouncement.php">Announcements</a></li>
				<li><a href="../document.php">Documents</a></li>
			</ul>
		</nav>
	</aside>

	<main class="cc-main">
		<h2>Welcome, Admin <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
		<p>This is your company admin dashboard.</p>
	</main>
</div>
<?php include '../includes/footer.php'; ?>
