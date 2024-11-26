

<?php
// 定数がすでに定義されている場合は再定義しない
if (!defined('SERVER')) {
    define('SERVER', 'mysql310.phy.lolipop.lan'); // サーバー名
}
if (!defined('DBNAME')) {
    define('DBNAME', 'LAA1516821-spotlink'); // データベース名
}
if (!defined('USER')) {
    define('USER', 'LAA1516821'); // ユーザー名
}
if (!defined('PASS')) {
    define('PASS', 'nomodon'); // パスワード
}

// PDO接続文字列を返す
$connect = "mysql:host=" . SERVER . ";dbname=" . DBNAME . ";charset=utf8mb4";

?>
