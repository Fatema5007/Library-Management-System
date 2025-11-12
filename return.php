<?php
require __DIR__ . '/db.php';

$user = $_SESSION['user'] ?? null;
if (!$user) { $_SESSION['flash']='Please login.'; header('Location: login.php'); exit; }

$txId = isset($_GET['tx_id']) ? (int)$_GET['tx_id'] : 0;
if ($txId <= 0) { $_SESSION['flash']='Invalid transaction.'; header('Location: user_dashboard.php'); exit; }

$st = $mysqli->prepare("
  SELECT t.id, t.book_id, b.title
  FROM transactions t
  JOIN books b ON b.id = t.book_id
  WHERE t.id=? AND t.user_id=? AND t.returned_at IS NULL
  LIMIT 1
");
$st->bind_param('ii', $txId, $user['id']);
$st->execute();
$row = $st->get_result()->fetch_assoc();
if (!$row) { $_SESSION['flash']='Nothing to return or not yours.'; header('Location: user_dashboard.php'); exit; }

$mysqli->begin_transaction();
$now = date('Y-m-d H:i:s');
$u1 = $mysqli->prepare("UPDATE transactions SET returned_at=? WHERE id=? AND returned_at IS NULL");
$u1->bind_param('si', $now, $txId);
$u1->execute();
if ($u1->affected_rows === 0) { $mysqli->rollback(); $_SESSION['flash'] = 'Could not process return.'; header('Location: user_dashboard.php'); exit; }

$u2 = $mysqli->prepare("UPDATE books SET copies_available = copies_available + 1 WHERE id=?");
$u2->bind_param('i', $row['book_id']);
$u2->execute();

$mysqli->commit();
$_SESSION['flash'] = 'Returned: ' . $row['title'] . '.';
header('Location: user_dashboard.php'); exit;
