<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../includes/db.php';
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

$errors = [];
$success = '';

/* ===============================
   CREATE ANNOUNCEMENT
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title      = trim($_POST['title'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $priority   = $_POST['priority'] ?? 'medium';
    $start_date = $_POST['start_date'] ?: null;
    $end_date   = $_POST['end_date'] ?: null;

    if (!$title || !$content) {
        $errors[] = "Title and content are required.";
    } else {
    $stmt = $db->prepare(""
      INSERT INTO announcements
      (title, content, company_id, created_by, priority, start_date, end_date)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    """);
    $stmt->execute([
      $title,
      $content,
      $_SESSION['company_id'],
      $_SESSION['user_id'],
      $priority,
      $start_date,
      $end_date
    ]);
        $success = "Announcement published successfully.";
    }
}

/* ===============================
   FETCH ANNOUNCEMENTS
================================ */
$stmt = $db->prepare(""
  SELECT a.*, u.name AS creator
  FROM announcements a
  JOIN users u ON a.created_by = u.id
  WHERE a.company_id = ?
  ORDER BY a.created_at DESC
""");
$stmt->execute([$_SESSION['company_id']]);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Announcements — CloudConnect</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link rel="stylesheet" href="/cloudconnect/assets/css/theme.css">

<style>
/* ===== LAYOUT ===== */
:root{
  --header-height:64px;
  --sidebar-width:260px;
}

.cc-main{
  margin-left:var(--sidebar-width);
  padding:32px;
  padding-top: calc(var(--header-height) + 24px);
  min-height: calc(100vh - var(--header-height));

.container{
  max-width:1100px;
  margin:0 auto;
}

/* ===== HEADER ===== */
.page-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  margin-bottom:24px;
}

.page-header h1{
  font-size:26px;
  font-weight:700;
}

.page-header p{
  color:var(--muted);
  margin-top:4px;
}

/* ===== CARD ===== */
.cc-card{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:18px;
  padding:28px;
  margin-bottom:28px;
}

/* ===== FORM ===== */
.form-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:18px;
}

.form-grid textarea{
  grid-column:1 / -1;
  min-height:120px;
}

label{
  font-size:13px;
  color:var(--muted);
  margin-bottom:6px;
  display:block;
}

input, textarea, select{
  width:100%;
  padding:12px 14px;
  border-radius:12px;
  border:1px solid var(--glass-border);
  background:transparent;
  color:var(--text);
  font-size:14px;
}

textarea{ resize:none }

/* ===== BUTTON ===== */
.btn-primary{
  grid-column:1 / -1;
  height:48px;
  border-radius:14px;
  border:none;
  background:linear-gradient(135deg,#5a8eff,#3b5bff);
  color:#fff;
  font-weight:700;
  cursor:pointer;
}

/* ===== ALERTS ===== */
.alert{
  padding:14px;
  border-radius:12px;
  margin-bottom:20px;
  font-size:14px;
}
.alert-success{
  background:rgba(46,204,113,.15);
  color:#2ecc71;
}
.alert-danger{
  background:rgba(231,76,60,.15);
  color:#e74c3c;
}

/* ===== ANNOUNCEMENTS ===== */
.announcement{
  padding:20px 0;
  border-bottom:1px solid var(--glass-border);
}
.announcement:last-child{ border-bottom:none }

.badge{
  display:inline-block;
  padding:4px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:700;
  margin-left:8px;
}

.low{ background:#6c757d;color:#fff }
.medium{ background:#ffc107;color:#000 }
.high{ background:#dc3545;color:#fff }

.meta{
  font-size:13px;
  color:var(--muted);
  margin-top:6px;
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
    padding-top: calc(var(--header-height) + 20px);
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
    <div>
      <h1>Announcements</h1>
      <p>Create and manage company-wide announcements</p>
    </div>
  </div>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <!-- CREATE ANNOUNCEMENT -->
  <div class="cc-card">
    <h3>Create New Announcement</h3>

    <form method="post" class="form-grid">
      <div>
        <label>Title</label>
        <input name="title" required>
      </div>

      <div>
        <label>Priority</label>
        <select name="priority">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
        </select>
      </div>

      <textarea name="content" placeholder="Announcement content..." required></textarea>

      <div>
        <label>Start Date</label>
        <input type="date" name="start_date">
      </div>

      <div>
        <label>End Date</label>
        <input type="date" name="end_date">
      </div>

      <button class="btn-primary">Publish Announcement</button>
    </form>
  </div>

  <!-- PREVIOUS ANNOUNCEMENTS -->
  <div class="cc-card">
    <h3>Previous Announcements</h3>

    <?php if ($announcements): ?>
      <?php foreach ($announcements as $a): ?>
        <div class="announcement">
          <strong><?= e($a['title']) ?></strong>
          <span class="badge <?= e($a['priority']) ?>">
            <?= ucfirst($a['priority']) ?>
          </span>

          <p><?= nl2br(e($a['content'])) ?></p>

          <div class="meta">
            Posted by <?= e($a['creator']) ?> •
            <?= date('d M Y, h:i A', strtotime($a['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="muted">No announcements published yet.</p>
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
