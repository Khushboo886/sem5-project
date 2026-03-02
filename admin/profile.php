<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../includes/db.php';
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

$errors = [];
$success = '';

/* ===============================
   FETCH ADMIN DATA
================================ */
$stmt = $db->prepare(""
  SELECT id, name, email, created_at
  FROM users
  WHERE id = ? AND role = 'Admin'
""");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
  header('Location: dashboard.php');
  exit;
}

/* ===============================
   UPDATE PROFILE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name  = trim($_POST['name']);
  $email = trim($_POST['email']);

  if (!$name || !$email) {
    $errors[] = "Name and Email are required.";
  }

  // check email duplicate (excluding self)
  $chk = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
  $chk->execute([$email, $_SESSION['user_id']]);
  if ($chk->fetch()) {
    $errors[] = "This email is already in use.";
  }

  if (!$errors) {
    $upd = $db->prepare("
      UPDATE users
      SET name = ?, email = ?
      WHERE id = ?
    ");
    $upd->execute([$name, $email, $_SESSION['user_id']]);

    // update session values
    $_SESSION['name']  = $name;
    $_SESSION['email'] = $email;

    $success = "Profile updated successfully.";
  }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Profile — CloudConnect</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

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

.page-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  margin-bottom:28px;
}

.page-header h1{
  font-size:26px;
  margin-bottom:4px;
}

.page-header p{
  color:var(--muted);
}

.panel{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  padding:24px;
  max-width:700px;
}

.form-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:18px;
}

.form-grid .full{
  grid-column:1 / -1;
}

label{
  font-size:13px;
  color:var(--muted);
  margin-bottom:6px;
  display:block;
}

input{
  width:100%;
  padding:12px;
  border-radius:10px;
  border:1px solid var(--glass-border);
  background:transparent;
  color:var(--text);
}

.form-actions{
  margin-top:24px;
  display:flex;
  gap:12px;
}

.info-box{
  margin-top:28px;
  background:rgba(255,255,255,.04);
  border:1px solid var(--glass-border);
  border-radius:12px;
  padding:16px;
  font-size:14px;
}

footer{
  margin-top:50px;
  text-align:center;
  font-size:13px;
  color:var(--muted);
}

@media(max-width:1000px){
  .cc-main{ margin-left:0; padding:20px; }
  .form-grid{ grid-template-columns:1fr; }
}
</style>
</head>

<body>

<?php include '../includes/admin_sidebar.php'; ?>

<main class="cc-main">

  <!-- HEADER -->
  <div class="page-header">
    <div>
      <h1>My Profile</h1>
      <p>Manage your admin account details</p>
    </div>
  </div>

  <?php if($errors): ?>
    <div class="alert error"><?= implode('<br>', $errors) ?></div>
  <?php endif; ?>

  <?php if($success): ?>
    <div class="alert success"><?= $success ?></div>
  <?php endif; ?>

  <!-- PROFILE FORM -->
  <div class="panel">
    <form method="post">
      <div class="form-grid">

        <div>
          <label>Full Name</label>
          <input type="text" name="name" value="<?= e($admin['name']) ?>" required>
        </div>

        <div>
          <label>Email</label>
          <input type="email" name="email" value="<?= e($admin['email']) ?>" required>
        </div>

        <div class="full">
          <label>Role</label>
          <input type="text" value="Administrator" disabled>
        </div>

      </div>

      <div class="form-actions">
        <button class="btn">💾 Save Changes</button>
        <a href="dashboard.php" class="btn-ghost">Cancel</a>
      </div>
    </form>

    <!-- INFO -->
    <div class="info-box">
      <strong>Account Created:</strong>
      <?= date('d M Y', strtotime($admin['created_at'])) ?><br><br>

      🔒 <strong>Password:</strong><br>
      To change your password, use the <em>Reset Password</em> option on the login page.
    </div>
  </div>

  <footer>
    © <?= date('Y') ?> CloudConnect — Built with care
  </footer>

</main>

<script src="/cloudconnect/assets/js/theme.js"></script>
</body>
</html>
