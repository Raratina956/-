<?php
session_start(); // 必要であればセッション開始
require 'db_connection.php'; // データベース接続 (仮)

// POSTで送られてきたデータを取得
$type = $_POST['type'];

switch ($type) {
    case 'all':
        $sql = $pdo->prepare('SELECT * FROM Favorite WHERE follow_id=?');
        $sql->execute([$_SESSION['user']['user_id']]);
        break;
    case 'teacher':
        $sql = $pdo->prepare('SELECT * FROM Favorite WHERE follow_id=? AND s_or_t=?');
        $sql->execute([$_SESSION['user']['user_id'], 1]);
        break;
    case 'student':
        $sql = $pdo->prepare('SELECT * FROM Favorite WHERE follow_id=? AND s_or_t=?');
        $sql->execute([$_SESSION['user']['user_id'], 0]);
        break;
    default:
        echo '不正なリクエスト';
        exit;
}

$list_raw = $sql->fetchAll(PDO::FETCH_ASSOC);

if ($list_raw) {
    echo '<table>';
    foreach ($list_raw as $favorite) {
        echo '<tr>';
        echo '<td>アイコン（仮）</td>';
        $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
        $sql_user->execute([$favorite['follower_id']]);
        $row_user = $sql_user->fetch();
        if($row_user['s_or_t'] === 0){
            echo '<td>', $row_user['user_name'], '</td>';
        }else{
            echo '<td>', $row_user['user_name'], '　先生</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo 'お気に入りのユーザーがいません';
}
?>
