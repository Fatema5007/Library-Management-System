<?php
require __DIR__ . '/db.php';

$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role'] ?? 'user') !== 'admin') {
    $_SESSION['flash'] = 'Admin only.';
    header('Location: index.php');
    exit;
}

$q = $mysqli->query("
    SELECT 
        t.id AS tx_id,
        u.name AS user_name,
        u.email AS email,
        b.title AS book_title,
        t.borrowed_at,
        t.due_at,
        t.returned_at
    FROM transactions t
    JOIN users u ON u.id = t.user_id
    JOIN books b ON b.id = t.book_id
    ORDER BY t.id DESC
");

$rows = $q->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Report</title>
<link rel="stylesheet" href="/Library_Management_System/styles.css?v=32">
</head>

<body>

<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Library</a>
    <nav class="menu">
      <a href="index.php">Home</a>
      <a href="admin_dashboard.php">Manage Books</a>
      <a href="admin_report.php" class="active">Borrow Report</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">

  <h2 style="margin:16px 0;">Borrowing Report </h2>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>User</th>
          <th>Email</th>
          <th>Book</th>
          <th>Borrowed</th>
          <th>Due</th>
          <th>Status</th>
        </tr>
      </thead>

      <tbody>
        <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $r): ?>
               <tr>
                 <td><?= htmlspecialchars($r['user_name']) ?></td>
                 <td><?= htmlspecialchars($r['email']) ?></td>
                 <td><?= htmlspecialchars($r['book_title']) ?></td>
                 <td><?= date('d M Y, h:i A', strtotime($r['borrowed_at'])) ?></td>
                 <td><?= date('d M Y, h:i A', strtotime($r['due_at'])) ?></td>
                 <td><?= $r['returned_at'] ? 'Returned' : 'Borrowed' ?></td>
               </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="muted">No borrowing records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</main>
<footer class="site-footer">
  <div class="container foot">
    <small>Have a question? Email us at: library123@gmail.com</small>
  </div>
</footer>


</body>
</html>
