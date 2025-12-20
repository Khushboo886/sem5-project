<?php
require_once '../includes/session.php';
requireAdmin();
require_once '../includes/db.php';
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

$success = '';
$errors = [];

/* -------------------------------------------------
   UPDATE COMPANY SETTINGS (BASIC)
------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {

    $company_name = trim($_POST['company_name']);
    $industry = trim($_POST['industry']);
    $website = trim($_POST['website']);

    if (!$company_name) {
        $errors[] = "Company name is required.";
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET name = ?, industry = ?, website = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $company_name,
            $industry ?: null,
            $website ?: null,
            $_SESSION['company_id']
        ]);
        $success = "Settings updated successfully.";
    }
}

/* -------------------------------------------------
   FETCH COMPANY DATA
------------------------------------------------- */
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$_SESSION['company_id']]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Settings — CloudConnect</title>
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

.page-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  margin-bottom:28px;
}

.page-header h1{font-size:26px;margin-bottom:4px}
.page-header p{color:var(--muted)}

.panel{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  padding:24px;
  margin-bottom:28px;
}

.panel h3{margin-bottom:4px}
.panel p{font-size:14px;color:var(--muted);margin-bottom:18px}

.form-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:20px;
}

.form-grid .full{grid-column:1/-1}

label{
  font-size:13px;
  color:var(--muted);
  display:block;
  margin-bottom:6px;
}

input{
  width:100%;
  padding:12px 14px;
  border-radius:10px;
  border:1px solid var(--glass-border);
  background:transparent;
  color:var(--text);
  font-size:14px;
}

.form-actions{
  margin-top:18px;
  text-align:right;
}

.divider{
  height:1px;
  background:var(--glass-border);
  margin:28px 0;
}

.info-box{
  background:rgba(255,255,255,.04);
  border:1px solid var(--glass-border);
  padding:16px;
  border-radius:12px;
  font-size:14px;
}

footer{
  margin-top:40px;
  text-align:center;
  font-size:13px;
  color:var(--muted);
}

/* ===== MOBILE FIX ===== */
@media(max-width:1000px){
  .cc-main{
    margin-left:0;
    padding:20px;
    padding-top: calc(var(--header-height) + 20px);
  }
  .form-grid{grid-template-columns:1fr;}
}
</style>
</head>

<body>

<?php include '../includes/admin_sidebar.php'; ?>

<main class="cc-main">

  <!-- HEADER -->
  <div class="page-header">
    <div>
      <h1>Settings</h1>
      <p>Manage company & system preferences</p>
    </div>
  </div>

  <!-- ALERTS -->
  <?php if($errors): ?>
    <div class="alert error"><?= implode('<br>', $errors) ?></div>
  <?php endif; ?>
  <?php if($success): ?>
    <div class="alert success"><?= $success ?></div>
  <?php endif; ?>

  <!-- COMPANY SETTINGS -->
  <div class="panel">
    <h3>Company Settings</h3>
    <p>Basic information about your organization</p>

    <form method="post">
      <div class="form-grid">

        <div>
          <label>Company Name</label>
          <input type="text" name="company_name"
                 value="<?= e($company['name']) ?>" required>
        </div>

        <div>
          <label>Industry</label>
          <input type="text" name="industry"
                 value="<?= e($company['industry']) ?>">
        </div>

        <div class="full">
          <label>Website</label>
          <input type="url" name="website"
                 value="<?= e($company['website']) ?>"
                 placeholder="https://example.com">
        </div>

      </div>

      <div class="form-actions">
        <button class="btn" type="submit" name="save_settings">
          💾 Save Changes
        </button>
      </div>
    </form>
  </div>

  <!-- ACCOUNT & SECURITY -->
  <div class="panel">
    <h3>Account & Security</h3>
    <p>Important system information</p>

    <div class="info-box">
      <strong>Admin Email:</strong> <?= e($_SESSION['email'] ?? '—') ?><br><br>
      <strong>Company ID:</strong> <?= e($_SESSION['company_id']) ?><br><br>
      <strong>Role:</strong> Administrator
    </div>

    <div class="divider"></div>

    <div class="info-box">
      🔒 <strong>Password Management</strong><br>
      To change your password, use the <em>Reset Password</em> option on the login page.
    </div>
  </div>

  <!-- SYSTEM INFO -->
  <div class="panel">
    <h3>System Information</h3>
    <p>Application details</p>

    <div class="info-box">
      <strong>Application:</strong> CloudConnect<br>
      <strong>Version:</strong> v1.0<br>
      <strong>Environment:</strong> Localhost / XAMPP<br>
      <strong>Last Login:</strong> <?= date('d M Y, h:i A') ?>
    </div>
  </div>

  <footer>
    © <?= date('Y') ?> CloudConnect — Built with care
  </footer>

</main>

<script src="/cloudconnect/assets/js/theme.js"></script>
</body>
</html>
