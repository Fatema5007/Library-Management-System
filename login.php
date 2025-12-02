<?php
require __DIR__ . '/db.php';

if (!empty($_SESSION['user'])) {
  if ($_SESSION['user']['role'] === 'admin') {
    header('Location: admin_dashboard.php');
  } else {
    header('Location: user_dashboard.php');
  }
  exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';

  if ($email === '' || $pass === '') $errors[] = 'Please fill all fields.';
  if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

  if (!$errors) {
    $stmt = $mysqli->prepare("SELECT id,name,email,password_hash,role FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    if (!$u) {
      $errors[] = 'Email not found.';
    } elseif (!password_verify($pass, $u['password_hash'])) {
      $errors[] = 'Incorrect password.';
    } else {
      $_SESSION['user'] = [
        'id' => (int)$u['id'],
        'name' => $u['name'],
        'email' => $u['email'],
        'role' => $u['role']
      ];

      if ($u['role'] === 'admin') {
        unset($_SESSION['intended_borrow_book_id']);
        header('Location: admin_dashboard.php');
        exit;
      }

      if (!empty($_SESSION['intended_borrow_book_id'])) {
        $id = (int)$_SESSION['intended_borrow_book_id'];
        unset($_SESSION['intended_borrow_book_id']);
        header('Location: borrow_confirm.php?book_id=' . $id);
        exit;
      }

      header('Location: user_dashboard.php');
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="/Library_Management_System/styles.css?v=50">
</head>
<body>

<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Library</a>
    <nav class="menu">
      <a href="index.php">Home</a>
      <a href="register.php">Register</a>
      <a href="login.php" class="active">Login</a>
    </nav>
  </div>
</header>

<main class="container">

  <div class="auth-wrap">
    <div class="auth-card">

      <h2 class="auth-title">Login</h2>

      <?php if ($errors): ?>
      <div class="alert">
        <ul>
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <form class="auth-form" method="post">
        <div class="field">
          <span>Email</span>
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="field">
          <span>Password</span>
          <input type="password" name="password" required>
        </div>

        <button class="button auth-submit">Login</button>

        <div class="auth-alt">
          Don't Have an Account? <a href="register.php">Register</a>
        </div>
      </form>

    </div>
  </div>

</main>

<footer class="site-footer">
  <div class="container foot">
    <small>Have a question? Email us at: library123@gmail.com</small>
  </div>
</footer>

</body>
</html>
