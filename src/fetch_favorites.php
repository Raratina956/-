<?php
require 'parts/auto-login.php';

// POSTで送られてきたデータを取得
$type = $_POST['type'];

switch ($type) {
    case 'all':
        $all_sql = $pdo->prepare('
        SELECT Favorite.*, Users.s_or_t, Users.user_name 
        FROM Favorite 
        JOIN Users ON Favorite.follower_id = Users.user_id 
        WHERE Favorite.follow_id = ?
    ');
        $all_sql->execute([$_SESSION['user']['user_id']]);
        $all_list = $all_sql->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'teacher':
        $student_sql = $pdo->prepare('
        SELECT Favorite.*, Users.s_or_t, Users.user_name 
        FROM Favorite 
        JOIN Users ON Favorite.follower_id = Users.user_id 
        WHERE Favorite.follow_id = ? AND Users.s_or_t = ?
    ');
        $student_sql->execute([$_SESSION['user']['user_id'], 0]);
        $student_list = $student_sql->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'student':
        $student_sql = $pdo->prepare('
        SELECT Favorite.*, Users.s_or_t, Users.user_name 
        FROM Favorite 
        JOIN Users ON Favorite.follower_id = Users.user_id 
        WHERE Favorite.follow_id = ? AND Users.s_or_t = ?
    ');
        $student_sql->execute([$_SESSION['user']['user_id'], 0]);
        $student_list = $student_sql->fetchAll(PDO::FETCH_ASSOC);
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
        if ($row_user['s_or_t'] === 0) {
            echo '<td>', $row_user['user_name'], '</td>';
        } else {
            echo '<td>', $row_user['user_name'], '　先生</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo 'お気に入りのユーザーがいません';
}
?>