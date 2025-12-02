<?php
require __DIR__ . '/db.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
  header('Location: login.php');
  exit;
}

$q = $mysqli->prepare("
  SELECT b.title, t.borrowed_at, t.due_at, t.returned_at
  FROM transactions t
  JOIN books b ON b.id = t.book_id
  WHERE t.user_id = ?
  ORDER BY t.id DESC
");
$q->bind_param('i', $user['id']);
$q->execute();
$rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Borrow History Report</title>
<link rel="stylesheet" href="/Library_Management_System/styles.css?v=40">

<style>
.page-wrap { width:min(900px,94%); margin:40px auto; }
.export-header { text-align:center; margin-bottom:20px; }

.export-table { width:100%; border-collapse:collapse; margin-top:10px; font-size:14px; }
.export-table, .export-table th, .export-table td { border:1px solid #000; }
.export-table th, .export-table td { padding:10px; text-align:left; }

.print-btn {
  padding:10px 16px;
  background:#fff;
  color:#000;
  border:1px solid #000;
  border-radius:8px;
  font-weight:600;
  cursor:pointer;
  margin-bottom:20px;
  transition:0.25s;
}
.print-btn:hover {
  background:#000;
  color:#000;
}

@media print {
  .site-header, .site-footer, .print-btn { display:none !important; }
}
</style>
</head>

<body>

<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Library</a>
    <nav class="menu">
      <a href="index.php">Home</a>
      <a href="user_dashboard.php">My Borrowings</a>
      <a class="active" href="export_pdf.php">Export PDF</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="page-wrap">

  <h2 class="export-header">Borrow History Report</h2>

  <a href="#" onclick="window.print()" class="pdf-btn">Export PDF</a>



  <table class="export-table">
    <tr>
      <th>Book Title</th>
      <th>Borrowed</th>
      <th>Due</th>
      <th>Status</th>
    </tr>

    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td><?= date('d M Y', strtotime($r['borrowed_at'])) ?></td>
        <td><?= date('d M Y', strtotime($r['due_at'])) ?></td>
        <td><?= $r['returned_at'] ? 'Returned' : 'Not Returned' ?></td>
      </tr>
    <?php endforeach; ?>

  </table>
</main>

<footer class="site-footer">
  <div class="container foot">
    <small>Have a question? Email us at: library123@gmail.com</small>
  </div>
</footer>

</body>
</html>
