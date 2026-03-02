<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
include '../includes/header.php';

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    header('Location: employees.php');
    exit;
}

/* ===============================
   FETCH EMPLOYEE DATA
*/
$stmt = $db->prepare(""
  SELECT 
    u.id,
    u.name,
    u.email,
    ed.employee_id,
    ed.department,
    ed.position,
    ed.join_date,
    ed.phone,
    ed.address,
    ed.emergency_contact,
    ed.emergency_phone
  FROM users u
  LEFT JOIN employee_details ed ON ed.user_id = u.id
  WHERE u.id = ? AND u.company_id = ? AND u.role = 'Employee'
");
$stmt->execute([$userId, $_SESSION['company_id']]);
$emp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$emp) {
    header('Location: employees.php');
    exit;
}

$errors = [];
$success = '';

/* ===============================
   UPDATE EMPLOYEE
   =============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);

    $employee_id       = trim($_POST['employee_id']);
    $department        = trim($_POST['department']);
    $position          = trim($_POST['position']);
    $join_date         = $_POST['join_date'] ?: null;
    $phone             = trim($_POST['phone']);
    $address           = trim($_POST['address']);
    $emergency_contact = trim($_POST['emergency_contact']);
    $emergency_phone   = trim($_POST['emergency_phone']);

    if (!$name || !$email) {
        $errors[] = "Name and Email are required.";
    }

    if (!$errors) {
        try {
      $db->beginTransaction();

      $db->prepare("UPDATE users SET name=?, email=? WHERE id=? AND company_id=?")
                ->execute([$name, $email, $userId, $_SESSION['company_id']]);

      $db->prepare("
              UPDATE employee_details SET
                employee_name=?,
                employee_id=?,
                department=?,
                position=?,
                join_date=?,
                phone=?,
                address=?,
                emergency_contact=?,
                emergency_phone=?
              WHERE user_id=?
            ")->execute([
                $name,
                $employee_id,
                $department,
                $position,
                $join_date,
                $phone,
                $address,
                $emergency_contact,
                $emergency_phone,
                $userId
            ]);

      $db->commit();
            $success = "Employee updated successfully.";
        } catch (Exception $ex) {
      $db->rollBack();
            $errors[] = "Error updating employee.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Employee — CloudConnect</title>
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

/* ===== FORM ===== */
.panel{
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:16px;
  padding:24px;
}

.form-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:18px;
}

.form-group{
  display:flex;
  flex-direction:column;
  gap:6px;
}

.form-group label{
  font-size:13px;
  color:var(--muted);
}

input, textarea{
  padding:12px;
  border-radius:10px;
  border:1px solid var(--glass-border);
  background:transparent;
  color:var(--text);
}

textarea{ resize:vertical }
.full{ grid-column:1 / -1 }

.form-actions{
  margin-top:24px;
  display:flex;
  gap:12px;
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
  .cc-main{ margin-left:0; padding:20px }
  .form-grid{ grid-template-columns:1fr }
}
</style>
</head>

<body>

<?php include '../includes/admin_sidebar.php'; ?>

<main class="cc-main">
  <div class="container">

    <div class="page-header">
      <div class="page-title">
        <h1>Edit Employee</h1>
        <p>Update employee details</p>
      </div>

      <a href="employees.php" class="btn btn-ghost">← Back</a>
    </div>

    <div class="panel">

      <?php if($errors): ?>
        <div class="alert error"><?= implode('<br>', $errors) ?></div>
      <?php endif; ?>

      <?php if($success): ?>
        <div class="alert success"><?= e($success) ?></div>
      <?php endif; ?>

      <form method="post">

        <div class="form-grid">

          <div class="form-group">
            <label>Employee Name</label>
            <input type="text" name="name" value="<?= e($emp['name']) ?>" required>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= e($emp['email']) ?>" required>
          </div>

          <div class="form-group">
            <label>Employee ID</label>
            <input type="text" name="employee_id" value="<?= e($emp['employee_id']) ?>">
          </div>

          <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" value="<?= e($emp['department']) ?>">
          </div>

          <div class="form-group">
            <label>Position</label>
            <input type="text" name="position" value="<?= e($emp['position']) ?>">
          </div>

          <div class="form-group">
            <label>Join Date</label>
            <input type="date" name="join_date" value="<?= e($emp['join_date']) ?>">
          </div>

          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= e($emp['phone']) ?>">
          </div>

          <div class="form-group full">
            <label>Address</label>
            <textarea rows="2" name="address"><?= e($emp['address']) ?></textarea>
          </div>

          <div class="form-group">
            <label>Emergency Contact</label>
            <input type="text" name="emergency_contact" value="<?= e($emp['emergency_contact']) ?>">
          </div>

          <div class="form-group">
            <label>Emergency Phone</label>
            <input type="text" name="emergency_phone" value="<?= e($emp['emergency_phone']) ?>">
          </div>

        </div>

        <div class="form-actions">
          <button class="btn btn-solid">Save Changes</button>
          <a href="employees.php" class="btn btn-ghost">Cancel</a>
        </div>

      </form>
    </div>

    <footer>
      © <?= date('Y') ?> CloudConnect — Built with care
    </footer>

  </div>
</main>

<script src="/cloudconnect/assets/js/theme.js"></script>
</body>
</html>
