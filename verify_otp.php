<?php
require_once 'includes/db.php';
require_once 'includes/session.php';
include 'includes/header.php';

$email  = $_GET['email'] ?? '';
$errors = [];

if (!$email) {
    header('Location: forget_password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp   = trim($_POST['otp']);
    $email = trim($_POST['email']);

    if (!preg_match('/^\d{6}$/', $otp)) {
        $errors[] = "Please enter a valid 6-digit OTP.";
    } else {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?
              AND reset_otp = ?
              AND otp_expiry > NOW()
            LIMIT 1
        ");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            header("Location: reset_password.php?email=" . urlencode($email));
            exit;
        } else {
            $errors[] = "Invalid or expired OTP.";
        }
    }
}
?>

<style>
/* ===============================
   CLOUDCONNECT VERIFY OTP – FINAL
================================ */

.cc-auth-page{
  min-height:calc(100vh - 120px);
  display:flex;
  align-items:center;
  justify-content:center;
  padding:32px 16px;
}

.cc-auth-card{
  width:100%;
  max-width:420px;
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  border-radius:20px;
  padding:36px 34px;
  box-shadow:0 30px 80px rgba(2,6,23,.22);
  text-align:center;
}

/* Headings */
.cc-auth-card h2{
  margin:0 0 10px;
  font-size:28px;
  font-weight:700;
}

.cc-auth-card .muted{
  color:var(--muted);
  font-size:14px;
  margin-bottom:26px;
  line-height:1.5;
}

/* Label */
label{
  display:block;
  text-align:left;
  margin-bottom:8px;
  font-size:13px;
  color:var(--muted);
}

/* OTP INPUT – FIXED */
input{
  width:100%;
  padding:16px;
  border-radius:14px;
  border:1.5px solid var(--glass-border);
  background:var(--input-bg, #fff);
  color:var(--text);
  font-size:22px;
  text-align:center;
  letter-spacing:6px;                 /* 🔥 reduced */
  font-weight:600;
  transition:border .2s ease, box-shadow .2s ease;
}

input::placeholder{
  letter-spacing:6px;
  color:#9ca3af;
}

input:focus{
  outline:none;
  border-color:var(--accent);
  box-shadow:0 0 0 4px rgba(79,124,255,.18);
}

/* Button */
.submit{
  margin-top:28px;
  width:100%;
  padding:14px;
  border-radius:16px;
  border:none;
  background:linear-gradient(135deg,#5a8eff,#3b5bff);
  color:#fff;
  font-weight:700;
  font-size:16px;
  cursor:pointer;
  transition:transform .15s ease, box-shadow .15s ease;
}

.submit:hover{
  transform:translateY(-1px);
  box-shadow:0 14px 36px rgba(79,124,255,.35);
}

/* Alerts */
.alert{
  margin-bottom:20px;
  padding:12px 14px;
  border-radius:12px;
  background:rgba(255,80,80,.15);
  color:#8b0000;
  font-size:14px;
  text-align:left;
}

/* OTP TIMER */
.timer{
  margin-top:20px;
  font-size:14px;
  color:var(--muted);
}

.resend{
  margin-top:6px;
  font-size:14px;
  color:var(--accent);
  opacity:.45;
  cursor:not-allowed;
}

.resend.enabled{
  opacity:1;
  cursor:pointer;
  text-decoration:underline;
}

/* Mobile */
@media(max-width:480px){
  .cc-auth-card{
    padding:28px 24px;
  }
}
</style>

<div class="cc-auth-page">

  <div class="cc-auth-card">

    <h2>Verify OTP</h2>
    <p class="muted">
      Enter the 6-digit OTP sent to<br>
      <strong><?= htmlspecialchars($email) ?></strong>
    </p>

    <?php if ($errors): ?>
      <div class="alert"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

      <label>One-Time Password</label>
      <input
        type="text"
        name="otp"
        maxlength="6"
        inputmode="numeric"
        pattern="[0-9]{6}"
        placeholder="••••••"
        autocomplete="one-time-code"
        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
        required
      >

      <button class="submit">Verify OTP</button>
    </form>

    <div class="timer">
      Resend OTP in <strong><span id="count">60</span>s</strong>
    </div>

    <div class="resend" id="resend">Resend OTP</div>

  </div>

</div>

<script>
let time = 60;
const count = document.getElementById('count');
const resend = document.getElementById('resend');

const timer = setInterval(() => {
  time--;
  count.textContent = time;
  if (time <= 0) {
    clearInterval(timer);
    resend.classList.add('enabled');
  }
}, 1000);

resend.onclick = () => {
  if (!resend.classList.contains('enabled')) return;
  location.href = "forget_password.php?email=<?= urlencode($email) ?>";
};
</script>

<?php include 'includes/footer.php'; ?>
