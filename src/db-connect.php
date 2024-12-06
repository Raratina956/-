<?php

$host = 'mysql310.phy.lolipop.lan';  // データベースのホスト名（例: localhost）
$dbname = 'LAA1516821-spotlink';     // データベース名
$user = 'LAA1516821';                // データベースユーザー名
$pass = 'nomodon';                   // データベースパスワード

// 定数が未定義の場合のみ定義
if (!defined('SERVER')) {
    define('SERVER', $host);
}
if (!defined('DBNAME')) {
    define('DBNAME', $dbname);
}
if (!defined('USER')) {
    define('USER', $user);
}
if (!defined('PASS')) {
    define('PASS', $pass);
}

// データベース接続設定
$connect = "mysql:host=$host;dbname=$dbname;charset=utf8";

?>
