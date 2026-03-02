<?php
// company_register.php
require_once 'includes/db.php';
require_once 'includes/session.php';
include 'includes/header.php';

$errors = [];
$company_name = $industry = $website = $admin_name = $admin_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name  = trim($_POST['company_name']);
    $industry      = trim($_POST['industry']);
    $website       = trim($_POST['website']);
    $admin_name    = trim($_POST['admin_name']);
    $admin_email   = trim($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];

    if (!$company_name || !$admin_name || !$admin_email || !$admin_password) {
        $errors[] = "Company name, admin name, email and password are required.";
    }

    if (empty($errors)) {
    $db->beginTransaction();
    try {
      $stmt = $db->prepare(
        "INSERT INTO companies (name, industry, website) VALUES (?, ?, ?)"
      );
      $stmt->execute([$company_name, $industry, $website]);
      $company_id = $db->lastInsertId();

      $hash = password_hash($admin_password, PASSWORD_DEFAULT);
      $stmt = $db->prepare(
        "INSERT INTO users (company_id, name, email, password, role)
         VALUES (?, ?, ?, ?, 'Admin')"
      );
      $stmt->execute([$company_id, $admin_name, $admin_email, $hash]);

      $db->commit();
            header('Location: login.php?registered=1');
            exit();
        } catch (Exception $e) {
      $db->rollBack();
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<style>
.container{
  min-height: calc(100vh - 64px); /* header height */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.card{
  width: 100%;
  max-width: 520px;
  background: var(--card-bg, #fff);
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 16px;
  padding: 26px;
  box-shadow: 0 12px 30px rgba(0,0,0,.08);

  /* ✅ ANIMATION */
  opacity: 0;
  transform: translateY(20px) scale(0.98);
  animation: cardFadeIn 0.6s ease-out forwards;
}

@keyframes cardFadeIn{
  to{
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

h2{margin:0 0 6px}
.muted{color:var(--muted, #6b7280);font-size:14px}

.form-group{margin-top:14px}
label{font-size:14px;display:block;margin-bottom:6px}

input{
  width:100%;
  padding:12px;
  border-radius:10px;
  border:1px solid var(--border, #e5e7eb);
  background:transparent;
  color:var(--text, #111);
}

button.submit{
  margin-top:18px;
  width:100%;
  padding:12px;
  border-radius:12px;
  border:none;
  background:var(--accent, #4f7cff);
  color:#fff;
  font-weight:700;
  cursor:pointer;
}

.alert{
  margin-top:12px;
  padding:12px;
  border-radius:10px;
  background:#fee2e2;
  color:#991b1b;
}

.login-link{
  margin-top:16px;
  text-align:center;
  font-size:14px;
}
.login-link a{
  color:var(--accent, #4f7cff);
  text-decoration:none;
  font-weight:600;
}
.login-link a:hover{text-decoration:underline}

footer{
  margin-top:20px;
  margin-bottom:12px;
  text-align:center;
  font-size:13px;
  color:var(--muted, #6b7280);
}
</style>
<div class="cc-app">
<div class="container">
  <div class="card">
    <h2>Register Company & Admin</h2>
    <p class="muted">Create your company workspace and admin account.</p>

    <?php if ($errors): ?>
      <div class="alert"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label>Company Name</label>
        <input name="company_name" required>
      </div>

      <div class="form-group">
        <label>Industry</label>
        <input name="industry">
      </div>

      <div class="form-group">
        <label>Website</label>
        <input name="website">
      </div>

      <hr style="margin:18px 0;border:none;border-top:1px solid var(--border)">

      <div class="form-group">
        <label>Admin Name</label>
        <input name="admin_name" required>
      </div>

      <div class="form-group">
        <label>Admin Email</label>
        <input type="email" name="admin_email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="admin_password" required>
      </div>

      <button class="submit">Register</button>
    </form>

    <div class="login-link">
      Already have an account?
      <a href="login.php">Login</a>
    </div>
    </div>
</div>

<footer>
  © <?php echo date('Y'); ?> CloudConnect — Built with care
</footer>

</body>
</html>