<?php
require_once '../includes/session.php';
requireEmployee();
require_once '../includes/db.php';
include '../includes/header.php';

$company_id = $_SESSION['company_id'] ?? null;

/* ---------- helper ---------- */
function snippet($text, $len = 160) {
    $t = strip_tags($text);
    return mb_strlen($t) <= $len ? $t : mb_substr($t, 0, $len) . '...';
}

/* ---------- FETCH ANNOUNCEMENTS ---------- */
$stmt = $pdo->prepare("
    SELECT title, content, created_at, priority
    FROM announcements
    WHERE company_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$company_id]);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/emp_sidebar.php'; ?>

<main class="cc-main">

  <!-- PAGE HEADER -->
  <div class="cc-page-header">
    <div>
      <h1>Announcements</h1>
      <p class="muted">Company updates & official notices</p>
    </div>
  </div>

  <!-- ANNOUNCEMENTS LIST -->
  <div class="cc-card cc-announcement-card">

    <?php if (!$announcements): ?>
      <p class="muted">No announcements available.</p>
    <?php else: ?>

      <?php foreach ($announcements as $a): ?>
        <div class="ann-item">

          <div class="ann-head">
            <h3><?= htmlspecialchars($a['title']) ?></h3>
            <span class="priority <?= htmlspecialchars($a['priority']) ?>">
              <?= ucfirst($a['priority']) ?>
            </span>
          </div>

          <p class="ann-date">
            <?= date('d M Y, h:i A', strtotime($a['created_at'])) ?>
          </p>

          <p class="ann-content">
            <?= nl2br(htmlspecialchars(snippet($a['content']))) ?>
          </p>

        </div>
      <?php endforeach; ?>

    <?php endif; ?>

  </div>

</main>

<style>
/* ===============================
   PROFESSIONAL ANNOUNCEMENTS UI
================================ */

.cc-main{
  margin-left:300px;
  margin-top:var(--header-height, 64px);
  padding:32px 36px 60px;
  min-height:calc(100vh - var(--header-height));
}

/* HEADER */
.cc-page-header{
  margin-bottom:26px;
}

.cc-page-header h1{
  font-size:28px;
  font-weight:800;
  margin-bottom:6px;
}

.cc-page-header p{
  font-size:14px;
  color:var(--muted);
}

/* CARD */
.cc-card{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:18px;
  padding:26px;
  box-shadow:0 14px 34px rgba(0,0,0,.08);
}

.cc-announcement-card{
  max-width:900px;
}

/* ANNOUNCEMENT ITEM */
.ann-item{
  padding:22px 0;
  border-bottom:1px solid var(--glass-border);
}

.ann-item:last-child{
  border-bottom:none;
}

.ann-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:12px;
}

.ann-head h3{
  font-size:18px;
  font-weight:700;
  margin:0;
}

/* PRIORITY BADGE */
.priority{
  padding:5px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:700;
}

.priority.low{
  background:#6c757d;
  color:#fff;
}

.priority.medium{
  background:#ffc107;
  color:#1a1a1a;
}

.priority.high{
  background:#dc3545;
  color:#fff;
}

/* META */
.ann-date{
  margin-top:6px;
  font-size:13px;
  color:var(--muted);
}

/* CONTENT */
.ann-content{
  margin-top:12px;
  font-size:14.5px;
  line-height:1.65;
  color:var(--text);
}

/* RESPONSIVE */
@media(max-width:1000px){
  .cc-main{
    margin-left:0;
    padding:22px;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
