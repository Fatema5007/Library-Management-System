<?php
require __DIR__ . '/db.php';

if (empty($_SESSION['user'])) {
  header('Location: login.php?next=user_dashboard.php');
  exit;
}
$user = $_SESSION['user'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="/Library_Management_System/styles.css">
  
</head>
<body>
  <div class="wrap">
    <h2>Welcome To User Dashboard</h2>
    <a class="btn" href="logout.php">Logout</a>
  </div>
</body>
</html>
