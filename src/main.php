<?php
require 'parts/auto-login.php';
require 'header.php';
?>
<br><br><br><br>ログイン後のページ
<p>
    <?php
    echo 'ユーザーID：', $_SESSION['user']['user_id'];
    echo 'ユーザー名：', $_SESSION['user']['user_name'];
    $sql_room = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
    $sql_room->execute([$_SESSION['user']['user_id']]);
    $row_room = $sql_room->fetch();
    if (!$row_room) {
        $current_name = '未登録';
    } else {
        $current_id = $row_room['classroom_id'];
        $sql_room = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
        $sql_room->execute([$current_id]);
        $row_room = $sql_room->fetch();
        $current_name = $row_room['classroom_name'];
    }
    echo '現在位置：', $current_name;
    // クッキーが設定されているかチェック
    if (isset($_COOKIE['remember_me_token'])) {
        $token = $_COOKIE['remember_me_token'];

        // トークンをデータベースで確認
        $sql = $pdo->prepare('SELECT * FROM Login_tokens WHERE token = ? AND expires_at > NOW()');
        $sql->execute([$token]);
        $login_token_row = $sql->fetch();

        if ($login_token_row) {
            // 自動ログインしている場合
            $is_auto_logged_in = true;

            // ユーザー情報を取得することも可能
            $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id = ?');
            $sql_user->execute([$login_token_row['user_id']]);
            $user_row = $sql_user->fetch();

            // 必要に応じてセッションにユーザー情報を格納
            $_SESSION['user'] = [
                'user_id' => $user_row['user_id'],
                'user_name' => $user_row['user_name']
            ];
        }
    }
    // 自動ログインの状態に応じた表示
    if ($is_auto_logged_in) {
        echo '自動ログイン中です。';
    } else {
        echo '自動ログインしていません';
    }
    ?>
</p>
<ul>
    <?php
    for ($i = 1; $i <= 6; $i++) {
        echo '<li>';
        echo '<form action="floor.php" method="post">';
        echo '<input type="hidden" name="floor" value="', $i, '">';
        echo '<input type="submit" value="', $i, '階">';
        echo '</form>';
        echo '</li>';
    }
    ?>
    <li><a href="my_tag.php">タグ作成</a></li>
    <li><a href="tag_list.php">タグ一覧</a></li>
    <li><a href="map.php">map</a></li>
    <li><a href="join_tag.php">参加タグ</a></li>
    <li><a href="favorite.php">お気に入り</a></li>
    <li><a href="announce.php">アナウンス</a></li>
    <li><a href="info.php">インフォ</a></li>
    <form action="search.php" method="post">
        <input type="text" name="search">
        <input type="submit" value="検索">
    </form>
    <?php echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '">自分のプロフィール</a></li>'; ?>
    <?php echo '<li><a href="chat-home.php?user_id=', $_SESSION['user']['user_id'], '">メッセージ</a></li>'; ?>
    <?php if ($_SESSION['user']['user_id'] == 7) {
        echo '<li><a href="admin.php">管理画面</a></li>';
    } ?>


    <!-- まだリンクがないので仮で作っときます -->
    <?php echo '<li><a href="user.php?user_id=4">公式生徒のプロフィール</a></li>'; ?>
    <?php echo '<li><a href="user.php?user_id=5">公式先生のプロフィール</a></li>'; ?>

</ul>