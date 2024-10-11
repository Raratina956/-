<?php
require 'parts/auto-login.php';

// POSTで送られてきたデータを取得
$type = isset($_POST['type']) ? $_POST['type'] : 'all'; // デフォルトを 'all' に設定
$list_raw = []; // 初期化

switch ($type) {
    case 'all':
        $all_sql = $pdo->prepare('
            SELECT Favorite.*, Users.s_or_t, Users.user_name 
            FROM Favorite 
            JOIN Users ON Favorite.follower_id = Users.user_id 
            WHERE Favorite.follow_id = ?
        ');
        $all_sql->execute([$_SESSION['user']['user_id']]);
        $list_raw = $all_sql->fetchAll(PDO::FETCH_ASSOC); // データを取得
        break;
    case 'teacher':
        $teacher_sql = $pdo->prepare('
            SELECT Favorite.*, Users.s_or_t, Users.user_name 
            FROM Favorite 
            JOIN Users ON Favorite.follower_id = Users.user_id 
            WHERE Favorite.follow_id = ? AND Users.s_or_t = ?
        ');
        $teacher_sql->execute([$_SESSION['user']['user_id'], 1]);
        $list_raw = $teacher_sql->fetchAll(PDO::FETCH_ASSOC); // データを取得
        break;
    case 'student':
        $student_sql = $pdo->prepare('
            SELECT Favorite.*, Users.s_or_t, Users.user_name 
            FROM Favorite 
            JOIN Users ON Favorite.follower_id = Users.user_id 
            WHERE Favorite.follow_id = ? AND Users.s_or_t = ?
        ');
        $student_sql->execute([$_SESSION['user']['user_id'], 0]);
        $list_raw = $student_sql->fetchAll(PDO::FETCH_ASSOC); // データを取得
        break;
    default:
        echo '不正なリクエスト';
        exit;
}

// 取得したデータを表示
if ($list_raw) {
    echo '<table>';
    foreach ($list_raw as $favorite) {
        echo '<tr>';
        echo '<td>アイコン（仮）</td>';
        echo '<td>', $favorite['user_name'], ($favorite['s_or_t'] === 0 ? '' : '　先生'), '</td>';
        ?>
        <form action="favorite.php" method="post">
            <input type="hidden" name="delete" value=<?php $favorite['favorite_id'] ?>>
            <input type="submit" value="削除">
        </form>
        <?php
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo 'お気に入りのユーザーがいません';
}
?>
