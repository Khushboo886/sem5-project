<?php
require_once '../includes/session.php';
requireEmployee();
require_once '../includes/db.php';

$name = $_SESSION['name'] ?? 'Employee';

/* FETCH ACTIVE ANNOUNCEMENTS */
$annStmt = $pdo->prepare("
  SELECT title, content, priority
  FROM announcements
  WHERE company_id = ?
    AND (start_date IS NULL OR start_date <= CURDATE())
    AND (end_date IS NULL OR end_date >= CURDATE())
  ORDER BY 
    FIELD(priority,'high','medium','low'),
    created_at DESC
");
$annStmt->execute([$_SESSION['company_id']]);
$announcements = $annStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<!-- Background shapes -->
<div class="bg-shape"></div>
<div class="bg-shape-2"></div>

<?php include '../includes/emp_sidebar.php'; ?>

<main class="cc-main">

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-title">
      <h1>Welcome, <?= htmlspecialchars($name) ?> 👋</h1>
      <p>Your employee dashboard overview</p>
    </div>
  </div>

  <!-- DASHBOARD GRID -->
  <div class="cc-grid">

    <!-- Attendance Summary -->
    <div class="panel">
      <h3>Attendance Summary</h3>

      <div class="metric">
        <span>Total Days</span>
        <strong>0</strong>
      </div>

      <div class="chips">
        <span class="chip green">Present: 0</span>
        <span class="chip yellow">Late: 0</span>
        <span class="chip red">Absent: 0</span>
      </div>
    </div>

    <!-- Leave Summary -->
    <div class="panel">
      <h3>Leave Summary</h3>

      <div class="chips">
        <span class="chip green">Approved: 0</span>
        <span class="chip yellow">Pending: 0</span>
        <span class="chip red">Rejected: 0</span>
      </div>
    </div>

    <!-- Profile Shortcut (NO NAME/EMAIL SHOWN) -->
    <div class="panel profile-card">
      <h3>My Profile</h3>

      <p class="muted">
        View and manage your personal details, password and settings.
      </p>

      <a href="profile.php" class="btn btn-primary profile-btn">
        View Full Profile →
      </a>
    </div>

  </div>

  <!-- RECENT ANNOUNCEMENTS -->
  <div class="cc-card">
    <h3>Recent Announcements</h3>

    <?php if ($announcements): ?>
      <?php foreach ($announcements as $a): ?>
        <div class="cc-announcement <?= htmlspecialchars($a['priority']) ?>">
          <h4><?= htmlspecialchars($a['title']) ?></h4>
          <p><?= nl2br(htmlspecialchars($a['content'])) ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="muted">No announcements yet.</p>
    <?php endif; ?>
  </div>

</main>

<script src="/cloudconnect/assets/js/theme.js"></script>

<style>
/* ===============================
   PROFESSIONAL UI / UX
================================ */

:root{
  --header-height:64px;
  --sidebar-width:260px;
}

/* MAIN */
.cc-main{
  margin-left:var(--sidebar-width);
  margin-top:var(--header-height);
  padding:32px 36px 60px;
  min-height:calc(100vh - var(--header-height));
}

/* HEADER */
.page-header{
  margin-bottom:28px;
}

.page-title h1{
  font-size:28px;
  font-weight:800;
}

.page-title p{
  color:var(--muted);
  font-size:15px;
}

/* GRID */
.cc-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
  gap:22px;
}

/* PANELS */
.panel{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:18px;
  padding:22px;
  box-shadow:0 12px 30px rgba(0,0,0,.08);
  transition:.25s ease;
}

.panel:hover{
  transform:translateY(-4px);
  box-shadow:0 20px 44px rgba(0,0,0,.12);
}

.panel h3{
  font-size:18px;
  font-weight:700;
  margin-bottom:10px;
}

/* METRIC */
.metric{
  display:flex;
  justify-content:space-between;
  margin:16px 0;
}

.metric strong{
  font-size:26px;
}

/* CHIPS */
.chips{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}

.chip{
  padding:6px 14px;
  border-radius:999px;
  font-size:13px;
  font-weight:700;
}

.green{ background:#e9f9f0; color:#1e8449; }
.yellow{ background:#fff6da; color:#7d6608; }
.red{ background:#fdecea; color:#922b21; }

/* PROFILE CARD */
.profile-card p{
  font-size:14.5px;
  color:var(--muted);
}

.profile-btn{
  margin-top:14px;
  border-radius:12px;
  font-weight:700;
}

/* ANNOUNCEMENTS */
.cc-card{
  margin-top:30px;
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:18px;
  padding:24px;
}

.cc-announcement{
  padding:14px 0;
  border-bottom:1px solid var(--glass-border);
}

.cc-announcement:last-child{ border-bottom:none }

.cc-announcement h4{
  font-size:16px;
  margin-bottom:4px;
}

.cc-announcement.high{ border-left:4px solid #ff6b6b; padding-left:12px }
.cc-announcement.medium{ border-left:4px solid #f1c40f; padding-left:12px }
.cc-announcement.low{ border-left:4px solid #4f8bff; padding-left:12px }

/* MOBILE */
@media(max-width:1000px){
  .cc-main{
    margin-left:0;
    padding:22px;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
