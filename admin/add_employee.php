<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $employee_id       = trim($_POST['employee_id'] ?? '');
    $department        = trim($_POST['department'] ?? '');
    $position          = trim($_POST['position'] ?? '');
    $join_date         = trim($_POST['join_date'] ?? '');
    $phone             = trim($_POST['phone'] ?? '');
    $address           = trim($_POST['address'] ?? '');
    $emergency_contact = trim($_POST['emergency_contact'] ?? '');
    $emergency_phone   = trim($_POST['emergency_phone'] ?? '');

    if (!$name || !$email || !$password) {
        $errors[] = "Name, Email and Password are required.";
    }

  $chk = $db->prepare("SELECT id FROM users WHERE email = ?");
  $chk->execute([$email]);
    if ($chk->fetch()) {
        $errors[] = "This email is already registered.";
    }

    if (!$errors) {
    try {
      $db->beginTransaction();

            $hash = password_hash($password, PASSWORD_DEFAULT);

      $stmt = $db->prepare(""
        INSERT INTO users (company_id, name, email, password, role)
        VALUES (?, ?, ?, ?, 'Employee')
      """);
      $stmt->execute([
        $_SESSION['company_id'],
        $name,
        $email,
        $hash
      ]);

      $userId = $db->lastInsertId();

      $stmt = $db->prepare(""
                INSERT INTO employee_details
                (user_id, employee_name, employee_id, department, position, join_date, phone, address, emergency_contact, emergency_phone)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $name,
                $employee_id ?: null,
                $department ?: null,
                $position ?: null,
                $join_date ?: null,
                $phone ?: null,
                $address ?: null,
                $emergency_contact ?: null,
                $emergency_phone ?: null
            ]);

            $db->commit();
            $success = true;

    } catch (Exception $e) {
      $db->rollBack();
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Employee — CloudConnect</title>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link rel="stylesheet" href="/assets/css/theme.css">

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

.page-header h1{
  font-size:26px;
  font-weight:700;
}

.page-header p{
  color:var(--muted);
  margin-top:4px;
}

/* ===== FORM CARD ===== */
.form-card{
  max-width:900px;
}

.panel{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  padding:22px;
  box-shadow:0 12px 30px rgba(2,6,23,.08);
}

/* ===== FORM ===== */
.form-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:18px;
}

.form-grid label{
  font-size:14px;
  color:var(--muted);
  display:block;
  margin-bottom:6px;
}

.form-grid input,
.form-grid textarea{
  width:100%;
  padding:12px;
  border-radius:10px;
  background:transparent;
  border:1px solid var(--glass-border);
  color:var(--text);
  font-size:14px;
}

.form-grid textarea{ resize:none; }

.full{ grid-column:1 / -1; }

.form-actions{
  margin-top:26px;
  display:flex;
  gap:12px;
}

/* ===== ALERTS ===== */
.alert{
  padding:14px;
  border-radius:10px;
  margin-bottom:18px;
}

.alert.error{
  background:rgba(255,80,80,.12);
  color:#ffb4b4;
}

.alert.success{
  background:rgba(46,204,113,.12);
  color:#7dffb4;
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
  .cc-main{margin-left:0;padding:20px;}
  .form-grid{grid-template-columns:1fr;}
}
</style>
</head>

<body>

<?php include '../includes/admin_sidebar.php'; ?>

<main class="cc-main">
  <div class="container">

    <!-- HEADER -->
    <div class="page-header">
      <div>
        <h1>Add Employee</h1>
        <p>Create login credentials and employee profile</p>
      </div>
    </div>

    <div class="panel form-card">

      <?php if ($errors): ?>
        <div class="alert error"><?= implode('<br>', $errors) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert success">
          Employee added successfully.<br>
          <a href="employees.php" style="color:var(--accent);font-weight:600">← Back to Employees</a>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="form-grid">

          <div>
            <label>Employee Name *</label>
            <input type="text" name="name" required>
          </div>

          <div>
            <label>Email (Login ID) *</label>
            <input type="email" name="email" required>
          </div>

          <div>
            <label>Password *</label>
            <input type="password" name="password" required>
          </div>

          <div>
            <label>Employee ID</label>
            <input type="text" name="employee_id">
          </div>

          <div>
            <label>Department</label>
            <input type="text" name="department">
          </div>

          <div>
            <label>Position</label>
            <input type="text" name="position">
          </div>

          <div>
            <label>Join Date</label>
            <input type="date" name="join_date">
          </div>

          <div>
            <label>Phone</label>
            <input type="text" name="phone">
          </div>

          <div class="full">
            <label>Address</label>
            <textarea rows="2" name="address"></textarea>
          </div>

          <div>
            <label>Emergency Contact</label>
            <input type="text" name="emergency_contact">
          </div>

          <div>
            <label>Emergency Phone</label>
            <input type="text" name="emergency_phone">
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-solid">Add Employee</button>
          <a href="employees.php" class="btn btn-ghost">Cancel</a>
        </div>
      </form>

    </div>

    <footer>
      © <?= date('Y') ?> CloudConnect — Built with care
    </footer>

  </div>
</main>

<script src="/assets/js/theme.js"></script>
</body>
</html>
