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
  // new fields
  $employee_id = trim($_POST['employee_id'] ?? '');
  $department = trim($_POST['department'] ?? '');
  $position = trim($_POST['position'] ?? '');
  $join_date = trim($_POST['join_date'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $emergency_contact = trim($_POST['emergency_contact'] ?? '');
  $emergency_phone = trim($_POST['emergency_phone'] ?? '');

  if (!$name || !$email || !$password) {
    $errors[] = "Name, email and password are required.";
  } else {
    try {
      $pdo->beginTransaction();

      // insert into users
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'Employee')");
      $stmt->execute([$_SESSION['company_id'], $name, $email, $hash]);
      $userId = $pdo->lastInsertId();

 // insert into existing employee_details table
$ins = $pdo->prepare("INSERT INTO employee_details (user_id, employee_name, employee_id, department, position, join_date, phone, address, emergency_contact, emergency_phone) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$ins->execute([
  $userId,
  $employee_name ?: null,      // ✅ new field added
  $employee_id ?: null,
  $department ?: null,
  $position ?: null,
  $join_date ?: null,
  $phone ?: null,
  $address ?: null,
  $emergency_contact ?: null,
  $emergency_phone ?: null,
]);

      $pdo->commit();
      $success = "Employee added successfully!";
    } catch (Exception $e) {
      $pdo->rollBack();
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

  <div class="mb-3">
    <label class="form-label">Employee ID</label>
    <input class="form-control" name="employee_id">
  </div>

  <div class="mb-3">
    <label class="form-label">Department</label>
    <input class="form-control" name="department">
  </div>

  <div class="mb-3">
    <label class="form-label">Position</label>
    <input class="form-control" name="position">
  </div>

  <div class="mb-3">
    <label class="form-label">Join Date</label>
    <input class="form-control" name="join_date" type="date">
  </div>

  <div class="mb-3">
    <label class="form-label">Phone</label>
    <input class="form-control" name="phone">
  </div>

  <div class="mb-3">
    <label class="form-label">Address</label>
    <textarea class="form-control" name="address"></textarea>
  </div>

  <div class="mb-3">
    <label class="form-label">Emergency Contact</label>
    <input class="form-control" name="emergency_contact">
  </div>

  <div class="mb-3">
    <label class="form-label">Emergency Phone</label>
    <input class="form-control" name="emergency_phone">
  </div>

  <button class="btn btn-primary" type="submit">Add Employee</button>
</form>

<?php include '../includes/footer.php'; ?>
