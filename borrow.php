<?php
require __DIR__ . '/db.php';

$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;

if (empty($_SESSION['user'])) {
  $_SESSION['intended_borrow_book_id'] = $bookId;
  header('Location: login.php');
  exit;
}

$user = $_SESSION['user'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Borrow</title>
</head>
<body>
  <h1>Borrow Page</h1>
  <p><a href="logout.php">Logout</a></p>
</body>
</html>
