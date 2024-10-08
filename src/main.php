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
        $current = '未登録';
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
</ul>