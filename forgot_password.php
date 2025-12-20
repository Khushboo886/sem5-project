<?php
require_once 'includes/db.php';
require_once 'includes/session.php';
include 'includes/header.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!$email) {
        $errors[] = "Please enter your registered email.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $otp = random_int(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt = $pdo->prepare(
                "UPDATE users SET reset_otp = ?, otp_expiry = ? WHERE email = ?"
            );
            $stmt->execute([$otp, $expiry, $email]);

            mail(
                $email,
                "CloudConnect Password Reset OTP",
                "Your OTP is: $otp\nValid for 10 minutes.",
                "From: no-reply@cloudconnect.com"
            );

            header("Location: verify_otp.php?email=" . urlencode($email));
            exit;
        } else {
            $errors[] = "Email not found.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Forgot Password — CloudConnect</title>

<style>
:root{
  --bg-grad:linear-gradient(135deg,#f5f7fa,#e6ecf5,#dce3ff);
  --text:#0b1220;
  --muted:rgba(11,18,32,.65);
  --card-bg:#ffffff;
  --border:rgba(0,0,0,.08);
  --btn:linear-gradient(135deg,#5a8eff,#3b5bff);
}

*{box-sizing:border-box}

body{
  margin:0;
  font-family:Inter,system-ui,Arial;
  background:var(--bg-grad);
  color:var(--text);
}

/* ✅ PAGE WRAPPER */
.page{
  min-height:100vh;
  display:flex;
  flex-direction:column;
}

/* ✅ CENTER CARD */
.main{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:24px;
}

.card{
  width:100%;
  max-width:520px;
  background:var(--card-bg);
  border:1px solid var(--border);
  border-radius:18px;
  padding:28px;
  box-shadow:0 20px 50px rgba(0,0,0,.15);
}

h2{margin:0 0 6px}
.muted{color:var(--muted);font-size:14px}

.form-group{margin-top:16px}
label{font-size:14px;margin-bottom:6px;display:block}

input{
  width:100%;
  padding:12px;
  border-radius:10px;
  border:1px solid var(--border);
}

button.submit{
  margin-top:18px;
  width:100%;
  padding:12px;
  border-radius:14px;
  border:none;
  background:var(--btn);
  color:#fff;
  font-weight:700;
  cursor:pointer;
}

.alert{
  margin-top:12px;
  padding:12px;
  border-radius:10px;
  background:#ffe5e5;
  color:#842029;
}

.link{
  margin-top:14px;
  font-size:14px;
}
.link a{
  color:#4f7cff;
  font-weight:600;
  text-decoration:none;
}

/* ✅ FOOTER STICKS TO BOTTOM */
footer{
  text-align:center;
  font-size:13px;
  color:var(--muted);
  padding:16px 0;
}
</style>
</head>

<body>
<div class="page">

  <div class="main">
    <div class="card">
      <h2>Forgot Password</h2>
      <p class="muted">
        Enter your registered email and we’ll send you a verification OTP.
      </p>

      <?php if ($errors): ?>
        <div class="alert"><?= implode('<br>', $errors); ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="you@company.com" required>
        </div>

        <button class="submit">Send OTP</button>

        <div class="link">
          Remembered your password?
          <a href="login.php">Login</a>
        </div>
      </form>
    </div>
  </div>

  <footer>
    © <?= date('Y') ?> CloudConnect — Built with care
  </footer>

</div>
</body>
</html>
