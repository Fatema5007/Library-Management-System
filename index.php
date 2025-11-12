<?php
require __DIR__ . '/db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if (isset($_GET['q']) && $q === '') { header('Location: index.php'); exit; }

$books = [];
if ($q !== '') {
  $like = "%{$q}%";
  $stmt = $mysqli->prepare(
    "SELECT id, title, author, copies_total, copies_available
     FROM books
     WHERE title LIKE ? OR author LIKE ?
     ORDER BY id DESC"
  );
  $stmt->bind_param('ss', $like, $like);
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  $res = $mysqli->query(
    "SELECT id, title, author, copies_total, copies_available
     FROM books
     ORDER BY id DESC"
  );
}
if ($res && $res->num_rows > 0) {
  while ($row = $res->fetch_assoc()) $books[] = $row;
}

$flash = '';
if (!empty($_SESSION['flash'])) { $flash = $_SESSION['flash']; unset($_SESSION['flash']); }
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Library</title>
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
          <a href="admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])==='admin_dashboard.php'?'active':'' ?>">Admin Dashboard</a>
        <?php else: ?>
          <a href="user_dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])==='user_dashboard.php'?'active':'' ?>">My Borrowings</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="register.php" class="<?= basename($_SERVER['PHP_SELF'])==='register.php'?'active':'' ?>">Register</a>
        <a href="login.php" class="<?= basename($_SERVER['PHP_SELF'])==='login.php'?'active':'' ?>">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">
  <?php if ($flash): ?>
    <div class="alert"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <section class="hero">
    <div class="hero-wrap">
      <img src="images/hero.jpg" alt="Library shelves">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>Welcome to The Library</h1>
        <p>Borrow the right book on right Time — simple and fast.</p>
      </div>
    </div>
  </section>

  <section class="catalog">
    <div class="catalog-head">
      <h2>Available Books</h2>
      <form class="book-search" action="index.php" method="get">
        <input type="text" name="q" placeholder="Search title or author" value="<?= htmlspecialchars($q) ?>" aria-label="Search books">
        <button class="button" type="submit"><b>Search</b></button>
        <?php if ($q !== ''): ?><a class="button" href="index.php"><b>Clear</b></a><?php endif; ?>
      </form>
    </div>

    <?php if ($q !== ''): ?>
      <div class="search-hint">Showing results for: <strong><?= htmlspecialchars($q) ?></strong> (<?= count($books) ?> found)</div>
    <?php endif; ?>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:42%">Title</th>
            <th style="width:28%">Author</th>
            <th style="width:15%">Available</th>
            <th style="width:15%">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($books)): ?>
          <?php foreach ($books as $b):
            $available = (int)$b['copies_available'];
            $bookId = (int)$b['id'];
          ?>
            <tr>
              <td><?= htmlspecialchars($b['title']) ?></td>
              <td><?= htmlspecialchars($b['author']) ?></td>
              <td><?= $available ?> / <?= (int)$b['copies_total'] ?></td>
              <td>
                <?php if ($available > 0): ?>
                  <?php if ($user): ?>
                    <a class="button" href="borrow.php?book_id=<?= $bookId ?>">Borrow</a>
                  <?php else: ?>
                    <a class="button" href="borrow.php?book_id=<?= $bookId ?>">Borrow </a>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="button is-disabled" aria-disabled="true">Out of stock</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4" class="muted">No books found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<footer class="site-footer">
  <div class="container foot">
    <small>© <?= date('Y') ?> Department of CSE, NEUB | Library Management System</small>
  </div>
</footer>
</body>
</html>
