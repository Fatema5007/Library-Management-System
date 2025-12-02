<?php
require __DIR__ . '/db.php';

$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
if ($bookId <= 0) { header('Location: index.php'); exit; }

$user = $_SESSION['user'] ?? null;

if (!$user) {
    $_SESSION['intended_borrow_book_id'] = $bookId;
    header('Location: login.php');
    exit;
}

if (($user['role'] ?? 'user') === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}

$st = $mysqli->prepare("SELECT id, title, author, copies_available FROM books WHERE id=? LIMIT 1");
$st->bind_param('i', $bookId);
$st->execute();
$book = $st->get_result()->fetch_assoc();

if (!$book) { header('Location: index.php'); exit; }

$_SESSION['confirm_borrow'] = $bookId;
?>
<!doctype html>
<html>
<head>
<title>Confirm Borrow</title>
<link rel="stylesheet" href="/Library_Management_System/styles.css?v=40">
<style>
.confirm-box{max-width:420px;margin:60px auto;background:#fff;padding:22px;border:1px solid #000;border-radius:10px;box-shadow:0 4px 14px rgba(0,0,0,0.18);text-align:center;}
.confirm-title{font-size:22px;font-weight:800;margin-bottom:12px;}
.confirm-details{margin-bottom:15px;font-size:15px;line-height:1.5;}
.confirm-btns{display:flex;justify-content:center;gap:12px;margin-top:20px;}
</style>
</head>

<body>

<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Library</a>
    <nav class="menu">
      <a href="index.php">Home</a>
      <a href="user_dashboard.php">My Borrowings</a>
      <a class="active" href="#">Confirm Borrow</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <div class="confirm-box">
    <div class="confirm-title">Confirm Your Borrow!</div>

    <div class="confirm-details">
      <b><?= htmlspecialchars($book['title']) ?></b><br>
      Author: <?= htmlspecialchars($book['author']) ?><br>
      Available Copies: <?= (int)$book['copies_available'] ?>
    </div>

    <div class="confirm-btns">
      <a class="button" href="borrow.php?book_id=<?= $bookId ?>">Confirm</a>
      <a class="button" href="index.php">Cancel</a>
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
