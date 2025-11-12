<?php
require __DIR__ . '/db.php';

$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
if ($bookId <= 0) { $_SESSION['flash'] = 'Invalid book.'; header('Location: index.php'); exit; }

$user = $_SESSION['user'] ?? null;
if (!$user) {
  $_SESSION['intended_borrow_book_id'] = $bookId;
  $_SESSION['flash'] = 'Please login to borrow books.';
  header('Location: login.php'); exit;
}

$st = $mysqli->prepare("SELECT id, title, copies_available FROM books WHERE id=? LIMIT 1");
$st->bind_param('i', $bookId);
$st->execute();
$book = $st->get_result()->fetch_assoc();
if (!$book) { $_SESSION['flash']='Book not found.'; header('Location: index.php'); exit; }

$upd = $mysqli->prepare("UPDATE books SET copies_available = copies_available - 1 WHERE id=? AND copies_available > 0");
$upd->bind_param('i', $bookId);
$upd->execute();
if ($upd->affected_rows === 0) { $_SESSION['flash'] = 'Sorry, this book is currently out of stock.'; header('Location: index.php'); exit; }

$borrowedAt = date('Y-m-d H:i:s');
$dueAt = date('Y-m-d H:i:s', strtotime('+14 days'));
$tx = $mysqli->prepare("INSERT INTO transactions (user_id, book_id, borrowed_at, due_at) VALUES (?,?,?,?)");
$tx->bind_param('iiss', $_SESSION['user']['id'], $bookId, $borrowedAt, $dueAt);
$tx->execute();

$_SESSION['flash'] = 'Borrowed: ' . $book['title'] . ' (due ' . date('d M Y, h:i A', strtotime($dueAt)) . ').';
header('Location: user_dashboard.php'); exit;
