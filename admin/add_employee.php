<?php
require_once '../includes/session.php';
requireAdmin(); // Only admin can access this page
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/admin_sidebar.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_name = trim($_POST['name']);
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

    if (!$employee_name || !$email || !$password) {
        $errors[] = "Employee name, email, and password are required.";
    } else {
        try {
            $pdo->beginTransaction();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'Employee')");
            $stmt->execute([$_SESSION['company_id'], $employee_name, $email, $hash]);
            $userId = $pdo->lastInsertId();

            $ins = $pdo->prepare("INSERT INTO employee_details (user_id, employee_name, employee_id, department, position, join_date, phone, address, emergency_contact, emergency_phone) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([
                $userId,
                $employee_name ?: null,
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

<main class="cc-main">
  <h1 class="admin-dashboard-title">Add Employee</h1>

  <?php if ($errors): ?>
    <div class="alert error"><?= implode('<br>', $errors) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert success"><?= $success ?></div>
  <?php endif; ?>

  <form method="post" class="employee-form">
    <div class="form-grid">
      <div>
        <label>Employee Name</label>
        <input type="text" name="name" required>
      </div>

      <div>
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div>
        <label>Password</label>
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

      <div class="full-width">
        <label>Address</label>
        <textarea name="address" rows="2"></textarea>
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

    <button type="submit" class="btn-primary">Add Employee</button>
  </form>
</main>

<?php include '../includes/footer.php'; ?>
