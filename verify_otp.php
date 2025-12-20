<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

$email = $_GET['email'] ?? '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);

    $stmt = $pdo->prepare(
        "SELECT id FROM users 
         WHERE email = ? AND reset_otp = ? AND otp_expiry > NOW()"
    );
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();

    if ($user) {
        header("Location: reset_password.php?email=" . urlencode($email));
        exit;
    } else {
        $errors[] = "Invalid or expired OTP.";
    }
}
?>
<!doctype html>
<html>
<head>
<title>Verify OTP — CloudConnect</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* SAME STYLE SYSTEM AS LOGIN */
<?php include 'ui_common_styles.php'; ?>
.timer{font-size:14px;color:var(--muted);margin-top:8px}
.resend{color:var(--accent);cursor:not-allowed;opacity:.5}
.resend.enabled{cursor:pointer;opacity:1}
</style>
</head>

<body>
<div class="container">
  <div class="card">
    <h2>Verify OTP</h2>
    <p class="muted">Enter the 6-digit OTP sent to your email.</p>

    <?php if ($errors): ?>
      <div class="alert"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post">
      <label>OTP</label>
      <input type="text" name="otp" maxlength="6" required>

      <button class="submit">Verify</button>
    </form>

    <div class="timer">
      Resend OTP in <span id="count">60</span>s
    </div>

    <div class="link resend" id="resend">Resend OTP</div>
  </div>
</div>

<script>
let t=60,btn=document.getElementById('resend'),c=document.getElementById('count');
let i=setInterval(()=>{
  t--;c.textContent=t;
  if(t<=0){clearInterval(i);btn.classList.add('enabled');btn.style.cursor='pointer';}
},1000);

btn.onclick=()=>{
  if(!btn.classList.contains('enabled'))return;
  location.href="forget_password.php";
};
</script>
</body>
</html>
