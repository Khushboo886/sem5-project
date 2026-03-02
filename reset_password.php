<?php
require_once 'includes/db.php';

$email = $_GET['email'] ?? '';
$errors = [];

function isStrongPassword($p){
  return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/',$p);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $password = $_POST['password'];

  if (!isStrongPassword($password)) {
    $errors[] = "Password must be 8+ chars with upper, lower, number & special char.";
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare(
      "UPDATE users 
       SET password=?, reset_otp=NULL, otp_expiry=NULL 
       WHERE email=?"
    );
    $stmt->execute([$hash, $email]);

    header("Location: login.php?reset=success");
    exit;
  }
}
?>
<!doctype html>
<html>
<head>
  <title>Reset Password — CloudConnect</title>
</head>
<body>

<h2>Reset Password</h2>

<?php if ($errors): ?>
<p style="color:red"><?= implode('<br>', $errors) ?></p>
<?php endif; ?>

<form method="post">
  <div style="position:relative">
    <input type="password" id="password" name="password" placeholder="New Password" required>
    <span onclick="toggle()" style="cursor:pointer">👁️</span>
  </div>
  <button type="submit">Reset Password</button>
</form>

<script>
function toggle(){
  const p=document.getElementById('password');
  p.type=p.type==='password'?'text':'password';
}
</script>

</body>
</html>
