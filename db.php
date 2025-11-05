<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'library_db';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
  die('DB connection failed: ' . $mysqli->connect_error);
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
