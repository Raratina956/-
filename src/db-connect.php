
<?php


$host = 'mysql310.phy.lolipop.lan';  // データベースのホスト名（例: localhost）
$dbname = 'LAA1516821-spotlink';     // データベース名
$user = 'LAA1516821';                // データベースユーザー名
$pass = 'nomodon';                   // データベースパスワード

// 定数 SERVER を定義
define('SERVER', $host);
define('DBNAME', $dbname);
define('USER', $user);
define('PASS', $pass);

// データベース接続設定
$connect = "mysql:host=$host;dbname=$dbname;charset=utf8";

?>
