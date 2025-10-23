<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user with company info
    $stmt = $pdo->prepare("
        SELECT u.*, c.name AS company_name 
        FROM users u 
        JOIN companies c ON u.company_id = c.id 
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['company_name'] = $user['company_name'];

        if ($user['role'] === 'Admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: employee/dashboard.php');
        }
        exit();
    } else {
        $errors[] = "Invalid credentials.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
  /* Make page full height and footer stick at bottom */
  html, body {
    height: 100%;
    margin: 0;
  }

  /* Wrapper for content that pushes footer down */
  .page-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 64vh;
  }

  main {
    flex: 1; /* main content takes remaining space */
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  .login-form {
    width: 100%;
    max-width: 400px;
  }
</style>

<div class="page-wrapper">
  <main>
    <div class="login-form">
      <h2 class="mb-4">Login</h2>

      <?php if ($errors): ?>
        <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input class="form-control" name="email" type="email" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input class="form-control" name="password" type="password" required>
        </div>

        <button class="btn btn-primary w-100" type="submit">Login</button>
      </form>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
</div>
