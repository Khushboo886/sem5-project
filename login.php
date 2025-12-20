<?php
// login.php
require_once 'includes/db.php';
require_once 'includes/session.php';
include './includes/header.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // basic validation
    if (!$email || !$password) {
        $errors[] = 'Please enter both email and password.';
    } else {
        // Fetch user with company info
        $stmt = $pdo->prepare("SELECT u.*, c.name AS company_name FROM users u JOIN companies c ON u.company_id = c.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // regenerate session id for safety
            session_regenerate_id(true);
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
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<div class="cc-app">
  <div class="login-container">

    <div class="login-card">
      <h2>Welcome back</h2>
      <p class="muted">Sign in to your CloudConnect account to access your dashboard.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>

        <button class="btn-primary">Login</button>

        <div class="login-links">
          <a href="forgot_password.php">Forgot password?</a>
          <span>•</span>
          <a href="company_register.php">Register</a>
        </div>
      </form>
    </div>

  </div>

  <footer class="cc-footer">
    © <?= date('Y') ?> CloudConnect — Built with care
  </footer>
</div>


 <style>
.login-container{
  min-height: calc(100vh - 64px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.login-card{
  width: 100%;
  max-width: 420px;
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 20px 50px rgba(0,0,0,.15);

  /* animation */
  opacity: 0;
  transform: translateY(20px);
  animation: fadeUp .6s ease forwards;
}

@keyframes fadeUp{
  to{ opacity:1; transform:none }
}

.form-group{ margin-top:14px }
label{ display:block; font-size:14px; margin-bottom:6px }

input{
  width:100%;
  padding:12px;
  border-radius:10px;
  border:1px solid var(--border);
  background:transparent;
  color:var(--text);
}

.btn-primary{
  width:100%;
  margin-top:18px;
  padding:12px;
  border-radius:12px;
  background: var(--accent);
  color:#fff;
  font-weight:700;
  border:none;
  cursor:pointer;
}

.login-links{
  margin-top:14px;
  display:flex;
  justify-content:center;
  gap:8px;
  font-size:14px;
}

.login-links a{
  color: var(--accent);
  text-decoration:none;
  font-weight:600;
}

.cc-footer{
  text-align:center;
  font-size:13px;
  color:var(--muted);
  padding-bottom:10px;
}
</style>

  <script>
    // Theme toggle with persistence
    (function(){
      const toggleBtn = document.getElementById('themeToggle');
      const body = document.body;
      const saved = localStorage.getItem('cloudconnect_theme');
      if(saved === 'light'){
        body.classList.add('light');
        toggleBtn.textContent = '🌙 Dark Mode';
      } else if(saved === 'dark'){
        body.classList.remove('light');
        toggleBtn.textContent = '☀️ Light Mode';
      } else {
        const prefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
        if(prefersLight){ body.classList.add('light'); toggleBtn.textContent = '🌙 Dark Mode'; }
        else { body.classList.remove('light'); toggleBtn.textContent = '☀️ Light Mode'; }
      }

      toggleBtn.addEventListener('click', ()=>{
        const nowLight = body.classList.toggle('light');
        if(nowLight){
          toggleBtn.textContent = '🌙 Dark Mode';
          localStorage.setItem('cloudconnect_theme','light');
        } else {
          toggleBtn.textContent = '☀️ Light Mode';
          localStorage.setItem('cloudconnect_theme','dark');
        }
      });
    })();
  </script>