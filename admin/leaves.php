<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

// Filters
$statusFilter = $_GET['status'] ?? '';

$params = [];
$where = "u.company_id = ? ";
$params[] = $_SESSION['company_id'];

if ($statusFilter) {
  $where .= "AND l.status = ? ";
  $params[] = $statusFilter;
}

// Fetch leaves
$stmt = $db->prepare(""
  SELECT 
    l.id,
    u.name AS employee_name,
    l.leave_type,
    l.start_date,
    l.end_date,
    l.reason,
    l.status,
    l.created_at
  FROM leaves l
  INNER JOIN users u ON u.id = l.user_id
  WHERE $where
  ORDER BY l.created_at DESC
");
$stmt->execute($params);
$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Leaves — CloudConnect</title>
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
}

.container{
  max-width:1100px;
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
  overflow:hidden;
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

.status{
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
  display:inline-block;
}

.pending{ background:#f1c40f; color:#2c2c2c; }
.approved{ background:linear-gradient(90deg,#1db954,#39d353); color:#081018; }
.rejected{ background:#e74c3c; color:#fff; }

.actions{
  white-space:nowrap;
}

.actions a{
  margin-left:6px;
}

/* ===== EMPTY STATE ===== */
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

    <!-- HEADER -->
    <div class="page-header">
      <div class="page-title">
        <h1>Leaves</h1>
        <p>Review and manage employee leave requests</p>
      </div>
    </div>

    <!-- FILTER -->
    <form class="filters" method="get">
      <select name="status">
        <option value="">All Status</option>
        <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>Pending</option>
        <option value="approved" <?= $statusFilter==='approved'?'selected':'' ?>>Approved</option>
        <option value="rejected" <?= $statusFilter==='rejected'?'selected':'' ?>>Rejected</option>
      </select>

      <button class="btn">Apply</button>
      <a href="leaves.php" class="btn btn-ghost">Reset</a>
    </form>

    <!-- TABLE -->
    <div class="panel">
      <?php if($leaves): ?>
        <table>
          <thead>
            <tr>
              <th>Employee</th>
              <th>Leave Type</th>
              <th>Date Range</th>
              <th>Reason</th>
              <th>Status</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($leaves as $l): ?>
              <tr>
                <td><?= e($l['employee_name']) ?></td>
                <td><?= ucfirst(e($l['leave_type'])) ?></td>
                <td><?= e($l['start_date']) ?> → <?= e($l['end_date']) ?></td>
                <td><?= e($l['reason'] ?? '—') ?></td>
                <td>
                  <span class="status <?= e($l['status']) ?>">
                    <?= ucfirst($l['status']) ?>
                  </span>
                </td>
                <td class="actions" style="text-align:right">
                  <?php if($l['status'] === 'pending'): ?>
                    <a href="update_leave_status.php?id=<?= $l['id'] ?>&status=approved" class="btn btn-ghost">Approve</a>
                    <a href="update_leave_status.php?id=<?= $l['id'] ?>&status=rejected" class="btn btn-ghost">Reject</a>
                  <?php else: ?>
                    <span style="color:var(--muted);font-size:13px">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty">
          <div style="font-size:36px;margin-bottom:10px">📄</div>
          No leave requests found
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
