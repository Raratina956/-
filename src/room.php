<?php
require 'parts/auto-login.php';
require 'header.php'; // ヘッダー読み込み

function fix_url($url)
{
    return str_replace('&amp;', '&', $url);
}

// QRコードから読み取ったURLを修正
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $fixed_url = fix_url($_SERVER['REQUEST_URI']);
    parse_str(parse_url($fixed_url, PHP_URL_QUERY), $queryParams);
    $room_id = htmlspecialchars($queryParams['id']);
    $update_id = htmlspecialchars($queryParams['update']);
} else {
    $room_id = $_GET['id'];
    $update_id = $_GET['update'];
}

$sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
$sql->execute([$room_id]);
$row = $sql->fetch();
$room_name = $row['classroom_name'];
$floor = $row['classroom_floor'];

// 位置情報を更新するかどうか確認（0 = 更新しない, 1 = 更新）
if ($update_id == 1) {
    // 位置情報を登録してるかどうか確認
    $now_time = date("Y/m/d H:i:s");
    $point = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
    $point->execute([$_SESSION['user']['user_id']]);
    if ($point->rowCount() == 0) {
        // 位置情報が未登録の場合の処理　→　新規登録
        $newpoint = $pdo->prepare('INSERT INTO Current_location(user_id, classroom_id, logtime) VALUES (?, ?, ?)');
        $newpoint->execute([$_SESSION['user']['user_id'], $room_id, $now_time]);
        $current_location_id = $pdo->lastInsertId();
    } else {
        // 位置情報が登録済の場合の処理　→　更新
        $updatepoint = $pdo->prepare('UPDATE Current_location SET classroom_id=?, logtime=? WHERE user_id=?');
        $updatepoint->execute([$room_id, $now_time, $_SESSION['user']['user_id']]);
        $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE classroom_id=? AND user_id=?');
        $current_sql->execute([$room_id, $_SESSION['user']['user_id']]);
        $current_row = $current_sql->fetch();
        $current_location_id = $current_row['current_location_id'];
    }

    $favorite_user = $pdo->prepare('SELECT * FROM Favorite WHERE follower_id=?');
    $favorite_user->execute([$_SESSION['user']['user_id']]);
    $favorite_results = $favorite_user->fetchAll(PDO::FETCH_ASSOC);
    if ($favorite_results) {
        foreach ($favorite_results as $favorite_row) {
            $announce_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=? AND type=?');
            $announce_sql->execute([
                $favorite_row['follow_id'],
                2
            ]);
            if ($announce_sql->rowCount() == 0) {
                $new_announce = $pdo->prepare('INSERT INTO Announce_check(current_location_id, user_id, read_check, type) VALUES (?, ?, ?, ?)');
                $new_announce->execute([$current_location_id, $favorite_row['follow_id'], 0, 2]);
            } else {
                $update_announce = $pdo->prepare('UPDATE Announce_check SET current_location_id=?, read_check=? WHERE user_id=? AND type=?');
                $update_announce->execute([$current_location_id, 0, $favorite_row['follow_id'], 2]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/room.css">
    <title><?php echo htmlspecialchars($room_name); ?> - 位置登録</title>
</head>

<body>
    <main>
        <h1><?php echo htmlspecialchars($floor); ?>階</h1>
        <span><?php echo htmlspecialchars($room_name); ?></span>
        <?php
        // 現在の位置情報を取得するクエリ
        $point = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
        $point->execute([$_SESSION['user']['user_id']]);
        $current_location = $point->fetch();
        if ($current_location && $current_location['classroom_id'] == $room_id) {
            echo '<button class="room" disabled>登録済み</button>';
        } else {
            // 登録されているが、room_idが異なる場合
            if ($current_location) {
                echo '<form action="room.php?id=' . htmlspecialchars($room_id) . '&update=1" method="post">
                        <input type="hidden" name="judge" value="1">  <!-- 更新のためのフラグ -->
                        <input class="room" type="submit" value="位置情報を更新">
                      </form>';
            } else {
                // 登録されていない場合
                echo '<form action="room.php?id=' . htmlspecialchars($room_id) . '&update=1" method="post">
                        <input type="hidden" name="judge" value="0">
                        <input class="room" type="submit" value="位置登録">
                      </form>';
            }
        }
        ?>
        <!-- QR表示 -->
        <form id="qr-form" action="qr_show.php" method="post" target="_blank">
            <?php echo '<input type="hidden" name="custom_url" value="https://aso2201203.babyblue.jp/Nomodon/src/room.php?id=' . htmlspecialchars($room_id) . '&update=1">'; ?>
            <button type="submit">QR表示</button>
        </form>

        <!-- 教室にいるメンバーを表示 -->
        <?php
            // 教室にいるメンバーを持ってくる
            $users=$pdo->prepare('SELECT * FROM Current_location WHERE classroom_id=?');
            $users->execute([$room_id]);

            echo '<ul>';
            foreach($users as $user){

                //ユーザー情報を持ってくる
                $members=$pdo->prepare('select * from Users where user_id=?');
                $members->execute([$user['user_id']]);
                $member = $members->fetch(PDO::FETCH_ASSOC);

                //アイコン情報を持ってくる
                $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
                $iconStmt->execute([$user['user_id']]);
                $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);

                echo '<li>
                        <div class="profile-container"><div class="user-container">
                        <img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">
                        <a href="user.php?user_id=' . $user['classroom_id'] . '">', $member['user_name'] ,'</a>
                      </li>';
            }
            echo '</ul>';
        ?>
        <a href="main.php" class="back-link">メインへ</a>
    </main>
</body>

</html>