<?php
require __DIR__ . '/db.php';

if (!empty($_SESSION['user'])) {
  if (!empty($_SESSION['intended_borrow_book_id'])) {
    $id = (int)$_SESSION['intended_borrow_book_id'];
    unset($_SESSION['intended_borrow_book_id']);
    header('Location: borrow.php?book_id=' . $id);
    exit;
  }
  if (($_SESSION['user']['role'] ?? 'user') === 'admin') {
    header('Location: admin_dashboard.php'); exit;
  }
  header('Location: user_dashboard.php'); exit;
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $pass  = isset($_POST['password']) ? $_POST['password'] : '';

  if ($email === '' || $pass === '') {
    $errors[] = 'Please fill all fields.';
  }

  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
  }

  if (!$errors) {
    $stmt = $mysqli->prepare("SELECT id,name,email,password_hash,role FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res  = $stmt->get_result();
    $user = $res ? $res->fetch_assoc() : null;

    if ($user && password_verify($pass, $user['password_hash'])) {
      $_SESSION['user'] = [
        'id'    => (int)$user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role']
      ];
      $_SESSION['flash'] = 'Welcome back, ' . $user['name'] . '!';

      if (!empty($_SESSION['intended_borrow_book_id'])) {
        $id = (int)$_SESSION['intended_borrow_book_id'];
        unset($_SESSION['intended_borrow_book_id']);
        header('Location: borrow.php?book_id=' . $id); exit;
      }
      if (($_SESSION['user']['role'] ?? 'user') === 'admin') {
        header('Location: admin_dashboard.php'); exit;
      }
      header('Location: user_dashboard.php'); exit;
    } else {
      $errors[] = 'Invalid credentials.';
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="/Library_Management_System/styles.css?v=23">
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Library</a>
    <nav class="menu">
      <a href="index.php">Home</a>
      <a href="login.php" class="active">Login</a>
      <a href="register.php">Register</a>
    </nav>
  </div>
</header>

<main class="container">
  <div class="auth-wrap">
    <div class="auth-card">
      <h2 class="auth-title">Login</h2>
      <p class="auth-sub">Sign In to continue.</p>

      <?php if (!empty($errors)): ?>
        <div class="alert">
          <ul style="margin-left:18px;">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form class="auth-form" action="login.php" method="post" novalidate>
        <div class="field">
          <span>Email</span>
          <input type="email" placeholder="Enter Your Email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="field">
          <span>Password</span>
          <input type="password" placeholder="Enter Your Password" name="password" required>
        </div>
        <button class="button auth-submit" type="submit">Login</button>
        <div class="auth-alt">Don't Have an Account? <a href="register.php">Create an account</a></div>
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
