<?php
// company_register.php
require_once 'includes/db.php';
require_once 'includes/session.php';

$errors = [];
$company_name = $industry = $website = $admin_name = $admin_email = '';
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
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Register Company — CloudConnect</title>
  <meta name="description" content="Register your company and admin on CloudConnect" />
  <style>
    :root{
      --bg-grad: linear-gradient(135deg,#08122f 0%, #0b2a5f 30%, #3b1c6b 70%, #2b0f46 100%);
      --text: #E6EEF8;
      --muted: rgba(230,238,248,0.85);
      --card-bg: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
      --glass-border: rgba(255,255,255,0.03);
      --btn-grad: linear-gradient(135deg,#5a8eff,#3b5bff);
      --btn-shadow: rgba(47,91,255,0.35);
      --accent: #4f8bff;
    }
    body.light{
      --bg-grad: linear-gradient(135deg,#f5f7fa,#e6ecf5,#dce3ff);
      --text: #0b1220;
      --muted: rgba(11,18,32,0.65);
      --card-bg: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(245,247,255,0.6));
      --glass-border: rgba(13,30,60,0.06);
      --btn-grad: linear-gradient(135deg,#2f5bff,#5a8eff);
      --btn-shadow: rgba(47,91,255,0.12);
      --accent: #2f5bff;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{
      font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
      color:var(--text);
      background: var(--bg-grad);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      overflow-x:hidden;
      transition:background .45s ease,color .3s ease;
    }
    .wrap{max-width:1100px;margin:30px auto;padding:28px}
    .nav{display:flex;align-items:center;justify-content:space-between}
    .brand{display:flex;align-items:center;gap:12px}
    .logo{width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;background:rgba(255,255,255,0.04);backdrop-filter:blur(6px);}
    .brand h1{font-size:18px;color:var(--text);font-weight:600}
    .cta{display:flex;gap:12px;align-items:center}
    .btn{padding:10px 16px;border-radius:10px;border:0;cursor:pointer;font-weight:600;background:transparent;color:var(--text);border:1px solid rgba(255,255,255,0.06)}
    .primary-cta{padding:12px 20px;border-radius:12px;background:var(--btn-grad);color:#fff;border:1px solid rgba(255,255,255,0.12);box-shadow:0 10px 30px var(--btn-shadow)}

    .card-wrap{display:grid;grid-template-columns:1fr 500px;gap:32px;align-items:start;margin-top:28px}
    .panel{background:var(--card-bg);padding:26px;border-radius:14px;border:1px solid var(--glass-border);box-shadow:0 12px 30px rgba(2,6,23,0.35);transition:all .35s ease}
    .panel h2{margin-bottom:12px}
    .muted{color:var(--muted)}

    form .row{display:flex;gap:12px}
    .form-group{margin-bottom:14px}
    label{display:block;margin-bottom:6px;font-size:14px}
    input[type="text"], input[type="email"], input[type="password"]{width:100%;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:var(--text)}
    input.light-input{background:#fff;color:#111;border:1px solid rgba(13,30,60,0.06)}
    .btn-primary{padding:12px 18px;border-radius:10px;border:0;background:var(--btn-grad);color:#fff;font-weight:700}

    .mockup{border-radius:14px;padding:18px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border:1px solid var(--glass-border)}
    .mockup h4{margin-bottom:12px}
    .stats{display:flex;gap:12px;margin-bottom:14px}
    .stat{flex:1;padding:10px;border-radius:10px;background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.005));border:1px solid rgba(255,255,255,0.03)}
    .stat .num{font-weight:700}

    .alert{padding:12px;border-radius:8px;margin-bottom:12px}
    .alert-danger{background:rgba(255,80,80,0.12);border:1px solid rgba(255,80,80,0.12);color:#ffdcdc}

    @media (max-width:980px){.card-wrap{grid-template-columns:1fr;}
      .mockup{order:-1}
    }

  </style>
</head>
<body>
  <main class="wrap">
    <nav class="nav">
      <div class="brand">
        <div class="logo" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="28" height="28"><defs><linearGradient id="g1" x1="0" x2="1" y1="0" y2="1"><stop offset="0" stop-color="#77A9FF"/><stop offset="1" stop-color="#7C5BFF"/></linearGradient></defs><path d="M45 36c6 0 11-5 11-11s-5-11-11-11c-1.3 0-2.6.2-3.8.6A12 12 0 0 0 20 30.4 8 8 0 0 0 28 54h17z" fill="url(#g1)"/></svg></div>
        <h1>CloudConnect</h1>
      </div>

      <div class="cta">
        <button id="themeToggle" class="btn">🌙 Dark Mode</button>
        <a href="./cloudconnect.php" class="btn">← Back</a>
      </div>
    </nav>

    <div class="card-wrap">
      <section class="panel">
        <h2>Register Company & Admin</h2>
        <p class="muted">Create your company workspace and admin account to get started with CloudConnect.</p>

        <?php if ($errors): ?>
          <div class="alert alert-danger"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="form-group">
            <label>Company Name</label>
            <input name="company_name" type="text" value="<?php echo htmlspecialchars($company_name); ?>" required <?php echo isset($_POST)?'':' '; ?> />
          </div>

          <div class="form-group">
            <label>Industry</label>
            <input name="industry" type="text" value="<?php echo htmlspecialchars($industry); ?>" />
          </div>

          <div class="form-group">
            <label>Website</label>
            <input name="website" type="text" value="<?php echo htmlspecialchars($website); ?>" />
          </div>

          <hr style="margin:18px 0;border:none;border-top:1px solid rgba(255,255,255,0.04)" />
          <h3>Admin Account</h3>

          <div class="form-group">
            <label>Admin Name</label>
            <input name="admin_name" type="text" value="<?php echo htmlspecialchars($admin_name); ?>" required />
          </div>

          <div class="form-group">
            <label>Admin Email</label>
            <input name="admin_email" type="email" value="<?php echo htmlspecialchars($admin_email); ?>" required />
          </div>

          <div class="form-group">
            <label>Password</label>
            <input name="admin_password" type="password" required />
          </div>

          <div class="form-group">
            <label>Confirm Password</label>
            <input name="admin_password_confirm" type="password" required />
          </div>

          <div style="margin-top:12px">
            <button class="btn-primary" type="submit">Register</button>
          </div>
        </form>
      </section>

      <aside class="mockup">
        <h4>CloudConnect Preview</h4>
        <p class="muted">A quick look at your future company dashboard. The UI adapts to light/dark theme automatically.</p>
        <div class="stats" style="margin-top:12px">
          <div class="stat"><div class="num">0</div><div class="muted" style="font-size:12px">Employees</div></div>
          <div class="stat"><div class="num">0</div><div class="muted" style="font-size:12px">Departments</div></div>
          <div class="stat"><div class="num">0</div><div class="muted" style="font-size:12px">Open Roles</div></div>
        </div>

        <div style="margin-top:10px">
          <div style="background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.005));padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.03)">
            <strong>Admin</strong>
            <div class="muted" style="font-size:13px;margin-top:6px">You'll be the first admin — set up teams, roles and invite employees.</div>
          </div>
        </div>

      </aside>

    </div>

    <footer style="margin-top:22px;color:var(--muted);font-size:13px;text-align:center">
      © <?php echo date('Y'); ?> CloudConnect
    </footer>

  </main>

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
</body>
</html>