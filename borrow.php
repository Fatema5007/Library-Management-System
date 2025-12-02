<?php
require __DIR__ . '/db.php';

$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
if ($bookId <= 0) { header('Location:index.php'); exit; }

$user = $_SESSION['user'] ?? null;
if (!$user) {
    $_SESSION['intended_borrow_book_id']=$bookId;
    header('Location: login.php');
    exit;
}

if (($user['role'] ?? 'user') === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}

if (!isset($_SESSION['confirm_borrow']) || $_SESSION['confirm_borrow'] != $bookId) {
    header("Location: borrow_confirm.php?book_id=$bookId");
    exit;
}
unset($_SESSION['confirm_borrow']);

$limit = 3;

$check1 = $mysqli->prepare("SELECT COUNT(*) AS c FROM transactions WHERE user_id=? AND returned_at IS NULL");
$check1->bind_param('i', $user['id']);
$check1->execute();
$limitRow = $check1->get_result()->fetch_assoc();
if ($limitRow['c'] >= $limit) {
    $_SESSION['flash']='Borrow limit reached.';
    header('Location:user_dashboard.php');
    exit;
}

$check2 = $mysqli->prepare("SELECT id FROM transactions WHERE user_id=? AND book_id=? AND returned_at IS NULL LIMIT 1");
$check2->bind_param('ii', $user['id'], $bookId);
$check2->execute();
if ($check2->get_result()->fetch_assoc()) {
    $_SESSION['flash']='You already borrowed this book.';
    header('Location:user_dashboard.php');
    exit;
}

$st = $mysqli->prepare("SELECT id,title,copies_available FROM books WHERE id=? LIMIT 1");
$st->bind_param('i',$bookId);
$st->execute();
$book = $st->get_result()->fetch_assoc();

if (!$book) { header('Location:index.php'); exit; }

try {
    $mysqli->begin_transaction();

    $upd = $mysqli->prepare("UPDATE books SET copies_available=copies_available-1 WHERE id=? AND copies_available>0");
    $upd->bind_param('i',$bookId);
    $upd->execute();
    if ($upd->affected_rows === 0) {
        throw new Exception("Out of stock.");
    }

    $borrowedAt = date('Y-m-d H:i:s');
    $dueAt = date('Y-m-d H:i:s', strtotime('+14 days'));

    $tx = $mysqli->prepare("INSERT INTO transactions (user_id,book_id,borrowed_at,due_at) VALUES (?,?,?,?)");
    $tx->bind_param('iiss',$user['id'],$bookId,$borrowedAt,$dueAt);
    $tx->execute();

    $mysqli->commit();

    $_SESSION['flash']='Borrowed: '.$book['title'];
    header('Location:user_dashboard.php');
    exit;

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['flash']=$e->getMessage();
    header('Location:index.php');
    exit;
}
