<?php
require __DIR__ . '/db.php';

if (empty($_SESSION['user'])) {
  header('Location: login.php'); 
  exit;
}

if (($_SESSION['user']['role'] ?? 'user') !== 'admin') {
  http_response_code(403);
  echo "<h1>403</h1><p>Admins only.</p>";
  exit;
}

$admin = $_SESSION['user'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
</head>
<body>
  <h1> Welcome to Admin Dashboard</h1>
  <p><a href="logout.php">Logout</a></p>
</body>
</html>
