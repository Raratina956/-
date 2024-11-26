

<?php
// db-connect.php
$host = 'mysql310.phy.lolipop.lan';  // データベースのホスト名（例: localhost）
$dbname = 'LAA1516821-spotlink';  // データベース名
$user = 'LAA1516821';  // データベースユーザー名
$pass = 'nomodon';  // データベースパスワード

// データベース接続設定
$connect = "mysql:host=$host;dbname=$dbname;charset=utf8";
define('USER', $user);
define('PASS', $pass);
?>
