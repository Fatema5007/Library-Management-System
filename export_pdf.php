<?php
require __DIR__ . '/db.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
  $_SESSION['flash'] = 'Please login.';
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

<style>
body {
  font-family: Arial, sans-serif;
  margin: 40px;
  color: #000;
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

.button {
  display: inline-block;
  padding: 10px 16px;
  background: #000;
  color: #fff;
  text-decoration: none;
  border-radius: 6px;
  margin-bottom: 20px;
}

.button:hover { opacity: .85; }

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  font-size: 14px;
}

table, th, td {
  border: 1px solid #000;
}

th, td {
  padding: 10px;
  text-align: left;
}

@media print {
  .button { display: none; }
}
</style>

</head>
<body>

<h2>Borrow History Report</h2>

<a href="#" onclick="window.print()" class="button">Download PDF</a>

<table>
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

</body>
</html>
