<?php
require 'parts/auto-login.php';
?>
ログイン後のページ
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
    <form action="search.php"method="post">
        <input type="text" name="search">
        <input type="submit" value="検索"> 
    </form>
    <?php echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '">自分のプロフィール</a></li>'; ?>

    <!-- まだリンクがないので仮で作っときます -->
    <?php echo '<li><a href="user.php?user_id=4">公式生徒のプロフィール</a></li>'; ?>
    <?php echo '<li><a href="user.php?user_id=5">公式先生のプロフィール</a></li>'; ?>

</ul>