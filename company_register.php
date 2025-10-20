<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name']);
    $industry = trim($_POST['industry']);
    $website = trim($_POST['website']);
    $admin_name = trim($_POST['admin_name']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];

    if (!$company_name || !$admin_name || !$admin_email || !$admin_password) {
        $errors[] = "Company name, admin name, email and password are required.";
    }

    if (empty($errors)) {
        // Start transaction
        $pdo->beginTransaction();
        try {
            // Insert company
            $stmt = $pdo->prepare("INSERT INTO companies (name, industry, website) VALUES (?, ?, ?)");
            $stmt->execute([$company_name, $industry, $website]);
            $company_id = $pdo->lastInsertId();

            // Create admin user (hash password)
            $hash = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'Admin')");
            $stmt->execute([$company_id, $admin_name, $admin_email, $hash]);

            $pdo->commit();

            // Redirect to login
            header('Location: login.php?registered=1');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<h2>Register Company & Admin</h2>

<?php if ($errors): ?>
  <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="post">
  <div class="mb-3">
    <label class="form-label">Company Name</label>
    <input class="form-control" name="company_name" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Industry</label>
    <input class="form-control" name="industry">
  </div>
  <div class="mb-3">
    <label class="form-label">Website</label>
    <input class="form-control" name="website">
  </div>

  <hr>
  <h5>Admin Account</h5>
  <div class="mb-3">
    <label class="form-label">Admin Name</label>
    <input class="form-control" name="admin_name" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Admin Email</label>
    <input class="form-control" type="email" name="admin_email" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input class="form-control" type="password" name="admin_password" required>
  </div>
  <button class="btn btn-primary">Register</button>
</form>

<?php include 'includes/footer.php'; ?>