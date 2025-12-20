<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

$stmt = $pdo->prepare("
  SELECT u.id, u.name, u.email, ed.position
  FROM users u
  LEFT JOIN employee_details ed ON ed.user_id = u.id
  WHERE u.company_id = ? AND u.role='Employee'
  ORDER BY u.id DESC
");
$stmt->execute([$_SESSION['company_id']]);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Employees — CloudConnect</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link rel="stylesheet" href="/cloudconnect/assets/css/theme.css">

<style>
:root{
  --header-height:64px;
  --sidebar-width:260px;
}

/* ===== MAIN LAYOUT ===== */
.cc-main{
  margin-left:var(--sidebar-width);
  margin-top:var(--header-height);
  padding:28px 32px 60px;
  min-height:calc(100vh - var(--header-height));
  background:var(--bg-grad);
}

.container{
  max-width:1200px;
  margin:0 auto;
}

/* ===== PAGE HEADER ===== */
.page-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  margin-bottom:24px;
}

.page-title h1{
  font-size:26px;
  font-weight:700;
  margin-bottom:6px;
}

.page-title p{
  color:var(--muted);
  font-size:14px;
}

/* ===== ACTIONS ===== */
.actions{
  display:flex;
  gap:12px;
}

/* ===== PANEL ===== */
.panel{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  padding:18px;
  box-shadow:0 12px 30px rgba(2,6,23,.08);
}

/* ===== TABLE ===== */
table{
  width:100%;
  border-collapse:collapse;
}

thead th{
  font-size:13px;
  font-weight:600;
  color:var(--muted);
  padding:14px 12px;
  border-bottom:1px solid var(--glass-border);
}

tbody td{
  padding:14px 12px;
  font-size:14px;
  border-bottom:1px solid rgba(255,255,255,.06);
}

tbody tr:hover{
  background:linear-gradient(180deg, rgba(255,255,255,0.03), transparent);
}

.status-active{
  display:inline-block;
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
  background:linear-gradient(90deg,#1db954,#39d353);
  color:#081018;
}

.table-actions{
  text-align:right;
  white-space:nowrap;
}

/* ===== EMPTY STATE ===== */
.empty-state{
  text-align:center;
  padding:60px 20px;
  color:var(--muted);
}

.empty-state .icon{
  font-size:44px;
  margin-bottom:12px;
}

.empty-state .title{
  font-size:18px;
  font-weight:600;
  color:var(--text);
  margin-bottom:6px;
}

/* ===== FOOTER ===== */
footer{
  margin-top:40px;
  text-align:center;
  font-size:13px;
  color:var(--muted);
}

/* ===== RESPONSIVE ===== */
@media(max-width:1000px){
  .cc-main{
    margin-left:0;
    padding:20px;
  }
}
</style>
</head>

<body>

<?php include '../includes/admin_sidebar.php'; ?>

<main class="cc-main">
  <div class="container">

    <!-- PAGE HEADER -->
    <div class="page-header">
      <div class="page-title">
        <h1>Employees</h1>
        <p>Manage all employees in your company</p>
      </div>

      <div class="actions">
        <a href="add_employee.php" class="btn btn-solid">
          + Add New Employee
        </a>
      </div>
    </div>

    <!-- TABLE PANEL -->
    <div class="panel">

      <?php if($employees): ?>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Role</th>
            <th>Email</th>
            <th>Status</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($employees as $emp): ?>
          <tr>
            <td><?= e($emp['name']) ?></td>
            <td><?= e($emp['position'] ?? '—') ?></td>
            <td><?= e($emp['email']) ?></td>
            <td><span class="status-active">Active</span></td>
            <td class="table-actions">
              <a href="edit_employee.php?id=<?= $emp['id'] ?>" class="btn btn-ghost small">Edit</a>
              <a href="delete_employee.php?id=<?= $emp['id'] ?>"
                 class="btn btn-ghost small"
                 onclick="return confirm('Delete this employee?')">
                Delete
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="empty-state">
        <div class="icon">👥</div>
        <div class="title">No employees yet</div>
        <div class="sub">Click <strong>Add New Employee</strong> to get started.</div>
      </div>
      <?php endif; ?>

    </div>

    <footer>
      © <?= date('Y') ?> CloudConnect — Built with care
    </footer>

  </div>
</main>

<script src="/cloudconnect/assets/js/theme.js"></script>
</body>
</html>
