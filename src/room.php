<?php
require 'parts/auto-login.php';
require 'header.php'; // ヘッダー読み込み

$room_id = $_GET['id'];
$update_id = $_GET['update'];
$judge = isset($_POST['judge']) ? $_POST['judge'] : (isset($_GET['judge']) ? $_GET['judge'] : null);

// デバッグ用ログ
error_log("Room ID: " . $room_id);
error_log("Update ID: " . $update_id);
error_log("Judge: " . $judge);

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
    
    // デバッグ用ログ
    error_log("Point Row Count: " . $point->rowCount());
    
    if ($point->rowCount() == 0) {
        // 位置情報が未登録の場合の処理　→　新規登録
        $newpoint = $pdo->prepare('INSERT INTO Current_location(user_id, classroom_id, logtime) VALUES (?, ?, ?)');
        $newpoint->execute([$_SESSION['user']['user_id'], $room_id, $now_time]);
        
        // デバッグ用ログ
        error_log("New Point Inserted: " . $newpoint->rowCount());
    } else {
        // 位置情報が登録済の場合の処理　→　更新
        $updatepoint = $pdo->prepare('UPDATE Current_location SET classroom_id=?, logtime=? WHERE user_id=?');
        $updatepoint->execute([$room_id, $now_time, $_SESSION['user']['user_id']]);
        
        // デバッグ用ログ
        error_log("Point Updated: " . $updatepoint->rowCount());
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
                echo '<form action="room.php?id=' . $room_id . '&update=1" method="post">
                        <input type="hidden" name="judge" value="1">  <!-- 更新のためのフラグ -->
                        <input class="room" type="submit" value="位置情報を更新">
                      </form>';
            } else {
                // 登録されていない場合
                echo '<form action="room.php?id=' . $room_id . '&update=1" method="post">
                        <input type="hidden" name="judge" value="0">
                        <input class="room" type="submit" value="位置登録">
                      </form>';
            }
        }
        ?>
        <!-- QR表示 -->
        <form id="qr-form" action="qr_show.php" method="post" target="_blank">
            <input type="hidden" name="custom_url" value="https://aso2201203.babyblue.jp/Nomodon/src/room.php?id=<?php echo $room_id; ?>&update=1&judge=1">
            <button type="submit">QR表示</button>
        </form>
        <a href="main.php" class="back-link">メインへ</a>
    </main>
</body>
</html>
