<?php
require __DIR__ . '/db.php';
$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role'] ?? 'user') !== 'admin') { $_SESSION['flash']='Admin only.'; header('Location: index.php'); exit; }

$action = $_POST['action'] ?? '';
$title  = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$total  = isset($_POST['copies_total']) ? max(0, (int)$_POST['copies_total']) : 0;
$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($action === 'create' && $title && $author && $total > 0) {
  $stmt = $mysqli->prepare("INSERT INTO books (title,author,copies_total,copies_available) VALUES (?,?,?,?)");
  $stmt->bind_param('ssii', $title, $author, $total, $total);
  $stmt->execute();
  $_SESSION['flash'] = 'Book added.';
}

if ($action === 'update' && $id > 0 && $title && $author && $total >= 0) {
  $q = $mysqli->prepare("SELECT copies_total, copies_available FROM books WHERE id=?");
  $q->bind_param('i', $id);
  $q->execute();
  $old = $q->get_result()->fetch_assoc();
  if ($old) {
    $borrowed = max(0, (int)$old['copies_total'] - (int)$old['copies_available']);
    $newAvail = max(0, $total - $borrowed);
    $u = $mysqli->prepare("UPDATE books SET title=?, author=?, copies_total=?, copies_available=? WHERE id=?");
    $u->bind_param('ssiii', $title, $author, $total, $newAvail, $id);
    $u->execute();
    $_SESSION['flash'] = 'Book updated.';
  }
}

if ($action === 'delete' && $id > 0) {
  $d = $mysqli->prepare("DELETE FROM books WHERE id=?");
  $d->bind_param('i', $id);
  $d->execute();
  $_SESSION['flash'] = 'Book deleted.';
}

$rows = $mysqli->query("SELECT id,title,author,copies_total,copies_available FROM books ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin • Books</title>
<link rel="stylesheet" href="/Library_Management_System/styles.css?v=31">
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Library</a>
    <nav class="menu">
       <a href="index.php">Home</a>
       <a href="admin_dashboard.php" class="active">Manage Books</a>
       <a href="logout.php">Logout</a>
    </nav>

  </div>
</header>

<main class="container">
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>

  <h2 style="margin:16px 0;">Books</h2>

  <form class="auth-form" method="post" style="margin:12px 0 18px; max-width:520px;">
    <input type="hidden" name="action" value="create">
    <div class="field">
      <span>Title</span>
      <input name="title" required>
    </div>
    <div class="field">
      <span>Author</span>
      <input name="author" required>
    </div>
    <div class="field">
      <span>Total Copies</span>
      <input type="number" name="copies_total" min="1" placeholder="copies..." required>
    </div>
    <button class="button auth-submit" type="submit">Add Book</button>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Title</th><th>Author</th><th>Total</th><th>Available</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['title']) ?></td>
            <td><?= htmlspecialchars($b['author']) ?></td>
            <td><?= (int)$b['copies_total'] ?></td>
            <td><?= (int)$b['copies_available'] ?></td>
            <td>
              <form action="admin_dashboard.php" method="post" style="display:inline-block;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                <button class="button" onclick="return confirm('Delete this book?')">Delete</button>
              </form>
              <details style="display:inline-block;margin-left:6px;">
                <summary class="button">Edit</summary>
                <form action="admin_dashboard.php" method="post" class="auth-form" style="min-width:280px;padding:8px;">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                  <div class="field">
                    <span>Title</span>
                    <input name="title" value="<?= htmlspecialchars($b['title']) ?>" required>
                  </div>
                  <div class="field">
                    <span>Author</span>
                    <input name="author" value="<?= htmlspecialchars($b['author']) ?>" required>
                  </div>
                  <div class="field">
                    <span>Total Copies</span>
                    <input type="number" name="copies_total" min="0" value="<?= (int)$b['copies_total'] ?>" required>
                  </div>
                  <div class="field">
                    <span>Available (auto)</span>
                    <input type="number" value="<?= (int)$b['copies_available'] ?>" readonly>
                  </div>
                  <button class="button auth-submit" type="submit">Save</button>
                </form>
              </details>
            </td>
          </tr>
        <?php endforeach; ?>
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
