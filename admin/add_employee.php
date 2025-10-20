<?php
require_once '../includes/session.php';
requireAdmin();  // Only admin can access this page
require_once '../includes/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$name || !$email || !$password) {
        $errors[] = "All fields are required.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'Employee')");
            $stmt->execute([$_SESSION['company_id'], $name, $email, $hash]);
            $success = "Employee added successfully!";
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<h2>Add Employee</h2>

<?php if ($errors): ?>
  <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="post">
  <div class="mb-3">
    <label class="form-label">Employee Name</label>
    <input class="form-control" name="name" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" name="email" type="email" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Password</label>
    <input class="form-control" name="password" type="password" required>
  </div>

  <button class="btn btn-primary" type="submit">Add Employee</button>
</form>

<?php include '../includes/footer.php'; ?>
