<?php
// admin/dashboard.php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
include '../includes/header.php';

$companyId = $_SESSION['company_id'];

/* ===============================
   DASHBOARD METRICS (FIXED)
================================ */

// 1️⃣ Total Employees
$stmt = $pdo->prepare("
  SELECT COUNT(*) 
  FROM users 
  WHERE company_id = ? AND role = 'Employee'
");
$stmt->execute([$companyId]);
$totalEmployees = (int) $stmt->fetchColumn();

// 2️⃣ Active Attendance (Today)
$stmt = $pdo->prepare("
  SELECT COUNT(*) 
  FROM attendance a
  INNER JOIN users u ON u.id = a.user_id
  WHERE u.company_id = ?
    AND a.status = 'present'
    AND a.date = CURDATE()
");
$stmt->execute([$companyId]);
$activeAttendance = (int) $stmt->fetchColumn();

// 3️⃣ Pending Leave Requests
$stmt = $pdo->prepare("
  SELECT COUNT(*) 
  FROM leaves l
  INNER JOIN users u ON u.id = l.user_id
  WHERE u.company_id = ?
    AND l.status = 'pending'
");
$stmt->execute([$companyId]);
$pendingLeaves = (int) $stmt->fetchColumn();

// 4️⃣ Documents Shared
$stmt = $pdo->prepare("
  SELECT COUNT(*) 
  FROM documents 
  WHERE company_id = ?
");
$stmt->execute([$companyId]);
$documentsShared = (int) $stmt->fetchColumn();

// 5️⃣ Active Announcements (Date-based)
$stmt = $pdo->prepare("
  SELECT COUNT(*) 
  FROM announcements
  WHERE company_id = ?
    AND (start_date IS NULL OR start_date <= CURDATE())
    AND (end_date IS NULL OR end_date >= CURDATE())
");
$stmt->execute([$companyId]);
$activeAnnouncements = (int) $stmt->fetchColumn();

/* ===============================
   RECENT EMPLOYEES
================================ */
$stmt = $pdo->prepare("
  SELECT id, name, role, created_at
  FROM users
  WHERE company_id = ?
    AND role = 'Employee'
  ORDER BY id DESC
  LIMIT 5
");
$stmt->execute([$companyId]);
$recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt->execute([$companyId]);
$recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard — CloudConnect</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="/cloudconnect/assets/css/theme.css">

<style>
:root{
  --header-height:64px;
  --sidebar-width:280px;
}

.cc-main{
  margin-left:var(--sidebar-width);
  margin-top:var(--header-height);
  padding:28px;
  min-height:calc(100vh - var(--header-height));
}

.container{
  max-width:1200px;
  margin:auto;
}

.topbar h1{
  font-size:24px;
  font-weight:700;
}

.subtitle{
  font-size:14px;
  color:var(--muted);
  margin-top:4px;
}

/* ===== METRIC CARDS ===== */
.stats-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:16px;
  margin:24px 0;
}

.stat-card{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  padding:18px;
  text-decoration:none;
  color:inherit;
}

.stat-title{
  font-size:13px;
  color:var(--muted);
}

.stat-value{
  font-size:30px;
  font-weight:700;
  margin-top:6px;
}

.stat-meta{
  font-size:12px;
  color:var(--muted);
  margin-top:4px;
}

/* ===== TABLE ===== */
.section-title{
  margin:28px 0 12px;
  font-size:18px;
}

.recent-table{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  overflow:hidden;
}

table{
  width:100%;
  border-collapse:collapse;
}

th, td{
  padding:14px;
  font-size:14px;
}

th{
  color:var(--muted);
  text-align:left;
}

tr:not(:last-child){
  border-bottom:1px solid var(--glass-border);
}

footer{
  margin-top:60px;
  text-align:center;
  font-size:13px;
  color:var(--muted);
}

@media(max-width:900px){
  .cc-main{margin-left:0;}
}
</style>
</head>

<body>

<?php include '../includes/admin_sidebar.php'; ?>

<main class="cc-main">
  <div class="container">

    <!-- HEADER -->
    <div class="topbar">
      <h1>Admin Dashboard Overview</h1>
      <div class="subtitle">
        Welcome, <?= htmlspecialchars($_SESSION['name']) ?>
        — <?= htmlspecialchars($_SESSION['company_name']) ?>
      </div>
    </div>

    <!-- METRICS -->
    <div class="stats-grid">

      <a href="employees.php" class="stat-card">
        <div class="stat-title">Total Employees</div>
        <div class="stat-value"><?= $totalEmployees ?></div>
        <div class="stat-meta">Registered employees</div>
      </a>

      <a href="attendance.php" class="stat-card">
        <div class="stat-title">Active Attendance</div>
        <div class="stat-value"><?= $activeAttendance ?></div>
        <div class="stat-meta">Present today</div>
      </a>

      <a href="leaves.php" class="stat-card">
        <div class="stat-title">Pending Leaves</div>
        <div class="stat-value"><?= $pendingLeaves ?></div>
        <div class="stat-meta">Needs approval</div>
      </a>

      <a href="documents.php" class="stat-card">
        <div class="stat-title">Documents Shared</div>
        <div class="stat-value"><?= $documentsShared ?></div>
        <div class="stat-meta">Company files</div>
      </a>

      <a href="announcements.php" class="stat-card">
        <div class="stat-title">Active Announcements</div>
        <div class="stat-value"><?= $activeAnnouncements ?></div>
        <div class="stat-meta">Visible to employees</div>
      </a>

    </div>

    <!-- RECENT EMPLOYEES -->
    <h3 class="section-title">Recent Employees</h3>
    <div class="recent-table">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Role</th>
            <th>Date Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php if($recentUsers): foreach($recentUsers as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['role']) ?></td>
              <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            </tr>
          <?php endforeach; else: ?>
            <tr>
              <td colspan="3" style="text-align:center;color:var(--muted)">
                No employees found
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <footer>
      © <?= date('Y') ?> CloudConnect — Built with care
    </footer>

  </div>
</main>

<script src="/cloudconnect/assets/js/theme.js"></script>
</body>
</html>
