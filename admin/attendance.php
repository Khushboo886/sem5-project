<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../includes/db.php';
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

// Filters
$dateFilter = $_GET['date'] ?? '';
$userFilter = $_GET['user_id'] ?? '';

$params = [];
$where = "u.company_id = ? ";
$params[] = $_SESSION['company_id'];

if ($dateFilter) {
  $where .= "AND a.date = ? ";
  $params[] = $dateFilter;
}

if ($userFilter) {
  $where .= "AND u.id = ? ";
  $params[] = $userFilter;
}

// Fetch attendance
$stmt = $pdo->prepare("
  SELECT 
    u.name AS employee_name,
    a.date,
    a.check_in,
    a.check_out,
    a.status,
    a.remarks
  FROM attendance a
  INNER JOIN users u ON u.id = a.user_id
  WHERE $where
  ORDER BY a.date DESC, a.check_in DESC
");
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Employees for filter dropdown
$empStmt = $pdo->prepare("
  SELECT id, name 
  FROM users 
  WHERE company_id = ? AND role = 'Employee'
  ORDER BY name
");
$empStmt->execute([$_SESSION['company_id']]);
$employees = $empStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Attendance — CloudConnect</title>
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
  margin-bottom:22px;
}

.page-title h1{
  font-size:26px;
  font-weight:700;
  margin-bottom:6px;
}

.page-title p{
  color:var(--muted);
  font-size:15px;
}

/* ===== FILTERS ===== */
.filters{
  display:flex;
  gap:12px;
  flex-wrap:wrap;
  margin-bottom:18px;
}

.filters input,
.filters select{
  padding:10px 12px;
  border-radius:10px;
  border:1px solid var(--glass-border);
  background:var(--card-bg);
  color:var(--text);
}

/* ===== TABLE ===== */
.panel{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  padding:6px;
}

table{
  width:100%;
  border-collapse:collapse;
}

thead th{
  font-size:13px;
  color:var(--muted);
  text-align:left;
  padding:14px 12px;
  border-bottom:1px solid var(--glass-border);
}

tbody td{
  padding:16px 12px;
  border-bottom:1px solid rgba(255,255,255,0.06);
  font-size:14.5px;
}

/* ===== STATUS BADGES ===== */
.status{
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
  display:inline-block;
}

.present{ background:linear-gradient(90deg,#1db954,#39d353); color:#081018; }
.late{ background:#f1c40f; color:#2c2c2c; }
.absent{ background:#e74c3c; color:#fff; }
.half-day{ background:#3498db; color:#fff; }

/* ===== EMPTY ===== */
.empty{
  text-align:center;
  padding:60px;
  color:var(--muted);
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
  .cc-main{ margin-left:0; padding:20px; }
}
</style>
</head>

<body>

<?php include '../includes/admin_sidebar.php'; ?>

<main class="cc-main">
  <div class="container">

    <!-- HEADER -->
    <div class="page-header">
      <div class="page-title">
        <h1>Attendance</h1>
        <p>View and manage employee attendance</p>
      </div>
    </div>

    <!-- FILTERS -->
    <form class="filters" method="get">
      <input type="date" name="date" value="<?= e($dateFilter) ?>">

      <select name="user_id">
        <option value="">All Employees</option>
        <?php foreach($employees as $emp): ?>
          <option value="<?= $emp['id'] ?>" <?= $userFilter==$emp['id']?'selected':'' ?>>
            <?= e($emp['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btn btn-solid">Apply</button>
      <a href="attendance.php" class="btn btn-ghost">Reset</a>
    </form>

    <!-- TABLE -->
    <div class="panel">
      <?php if($records): ?>
        <table>
          <thead>
            <tr>
              <th>Employee</th>
              <th>Date</th>
              <th>Check In</th>
              <th>Check Out</th>
              <th>Status</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($records as $r): ?>
              <tr>
                <td><?= e($r['employee_name']) ?></td>
                <td><?= e($r['date']) ?></td>
                <td><?= e($r['check_in']) ?></td>
                <td><?= e($r['check_out'] ?? '—') ?></td>
                <td>
                  <span class="status <?= e($r['status']) ?>">
                    <?= ucfirst($r['status']) ?>
                  </span>
                </td>
                <td><?= e($r['remarks'] ?? '—') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty">
          <div style="font-size:36px;margin-bottom:10px">🕒</div>
          No attendance records found
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
