<?php
require __DIR__ . '/db.php';
$user = $_SESSION['user'] ?? null;
if (!$user) { $_SESSION['flash']='Please login.'; header('Location: login.php'); exit; }

$q = $mysqli->prepare("
  SELECT t.id AS tx_id, b.title, t.borrowed_at, t.due_at, t.returned_at
  FROM transactions t
  JOIN books b ON b.id = t.book_id
  WHERE t.user_id=?
  ORDER BY t.id DESC
");
$q->bind_param('i', $user['id']);
$q->execute();
$rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Dashboard</title>
<link rel="stylesheet" href="/Library_Management_System/styles.css?v=24">
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Library</a>
    <nav class="menu">
      <a href="index.php">Home</a>
      <a href="user_dashboard.php" class="active">My Borrowings</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>

  <h2 style="margin:16px 0;">My Borrow History</h2>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Title</th><th>Borrowed</th><th>Due</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php if ($rows): foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['title']) ?></td>
          <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($r['borrowed_at']))) ?></td>
          <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($r['due_at']))) ?></td>
          <td><?= $r['returned_at'] ? 'Returned' : 'Borrowed' ?></td>
          <td>
            <?php if (!$r['returned_at']): ?>
              <a class="button" href="return.php?tx_id=<?= (int)$r['tx_id'] ?>">Return</a>
            <?php else: ?>
              <span class="button is-disabled" aria-disabled="true">Done</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5" class="muted">No history yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<footer class="site-footer">
  <div class="container foot">
    <small>© <?= date('Y') ?> Department of CSE, NEUB | Library Management System</small>
  </div>
</footer>
</body>
</html>
