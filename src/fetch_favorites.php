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
echo '<style> table tr { border-bottom: 1px solid #000; /* 下線を追加 */ } table tr:last-child { border-bottom: none; /* 最後の行の下線を除去 */ } </style>';
// 取得したデータを表示
if ($list_raw) {
    echo '<table border="0" style="font-size: 16pt;">';
    foreach ($list_raw as $favorite) {
    echo '<tr>';
    $follower_id =  $favorite['follower_id'];
    $icon_sql = $pdo->prepare('SELECT * FROM Icon WHERE user_id=?');
    $icon_sql ->execute([$follower_id]);
    $icon_row = $icon_sql->fetch(PDO::FETCH_ASSOC);
    $icon_name = $icon_row['icon_name'];
    if($favorite['s_or_t'] == 0){
        echo '<td></td>';
    }else{
        echo '<td><img src="img/kakubo.jpg" style="margin-inline-end: 25px;"></td>';
    }
    echo '<td><a href="user.php?user_id=', $follower_id, '"><img src="'.$icon_name.'" width="100" height="100" class="usericon" title="'.$favorite['user_name'].'"></a></td>';
    echo '<td><a href="user.php?user_id=', $follower_id, '" class="atag">', $favorite['user_name'], ($favorite['s_or_t'] === 0 ? '' : '　先生'), '</a></td>';
    ?>
    
    <td>
        <button onclick="deleteFavorite(<?php echo $favorite['favorite_id']; ?>)" class="button_del">削除</button>
    </td>
    <?php
    echo '</tr>';
}

    echo '</table>';
} else {
    echo '<div class="non">お気に入りのユーザーがいません</div>';
}
?>