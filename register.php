<?php
require __DIR__ . '/db.php';

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = isset($_POST['name']) ? trim($_POST['name']) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $pass  = $_POST['password'] ?? '';
  $cpass = $_POST['confirm_password'] ?? '';

  if ($name === '' || $email === '' || $pass === '' || $cpass === '') $errors[] = 'Please fill all fields.';
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
  if ($pass !== '') {
    if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Za-z]/', $pass)) $errors[] = 'Password must contain at least one alphabet (A–Z or a–z).';
    if (!preg_match('/\d/', $pass)) $errors[] = 'Password must contain at least one digit (0–9).';
    if (!preg_match('/[^A-Za-z0-9]/', $pass)) $errors[] = 'Password must contain at least one special character (e.g., @ # $ % ! *).';
  }
  if ($pass !== '' && $cpass !== '' && $pass !== $cpass) $errors[] = 'Confirm password does not match.';

  if (!$errors) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'user')");
    $stmt->bind_param('sss', $name, $email, $hash);
    if ($stmt->execute()) {
      $newId = $stmt->insert_id;
      $q = $mysqli->prepare("SELECT id,name,email,role FROM users WHERE id=? LIMIT 1");
      $q->bind_param('i', $newId);
      $q->execute();
      $u = $q->get_result()->fetch_assoc();
      $_SESSION['user'] = [
        'id' => (int)$u['id'],
        'name' => $u['name'],
        'email' => $u['email'],
        'role' => $u['role']
      ];
      header('Location: user_dashboard.php'); exit;
    } else {
      $errors[] = ($stmt->errno === 1062) ? 'This email is already registered.' : 'Could not create account. Please try again.';
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register</title>
<link rel="stylesheet" href="/Library_Management_System/styles.css?v=30">
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Library</a>
    <?php $me = $_SESSION['user'] ?? null; ?>
    <nav class="menu">
      <a href="index.php" class="<?= basename($_SERVER['PHP_SELF'])==='index.php'?'active':'' ?>">Home</a>
      <?php if ($me): ?>
        <?php if (($me['role'] ?? 'user') === 'admin'): ?>
          <a href="admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin_dashboard.php'?'active':'' ?>">My Dashboard</a>
        <?php else: ?>
          <a href="user_dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])==='user_dashboard.php'?'active':'' ?>">My Borrowings</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="register.php" class="active">Register</a>
        <a href="login.php" class="<?= basename($_SERVER['PHP_SELF'])==='login.php'?'active':'' ?>">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">
  <div class="auth-wrap">
    <div class="auth-card">
      <h2 class="auth-title">Create an account</h2>
      <p class="auth-sub">Welcome! Continue Your Journey With Us</p>

      <?php if (!empty($errors)): ?>
        <div class="alert">
          <ul style="margin-left:18px;">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form class="auth-form" action="register.php" method="post" novalidate>
        <div class="field">
          <span>Name</span>
          <input type="text" name="name" placeholder="Enter Your Name" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        <div class="field">
          <span>Email</span>
          <input type="email" name="email" placeholder="Enter Your Email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="field">
          <span>Password</span>
          <input type="password" name="password" placeholder="Enter Your Password" required>
        </div>
        <div class="field">
          <span>Confirm Password</span>
          <input type="password" name="confirm_password" placeholder="Confirm Your Password" required>
        </div>
        <button class="button auth-submit" type="submit">Register</button>
        <div class="auth-alt">Already have an account? <a href="login.php">Login</a></div>
      </form>
    </div>
  </div>
</main>

<footer class="site-footer">
  <div class="container foot">
    <small>© <?= date('Y') ?> Department of CSE, NEUB | Library Management System</small>
  </div>
</footer>
</body>
</html>
