<?php
require 'parts/auto-login.php';
require 'header.php'; // ヘッダー読み込み
$room_id = $_GET['id'];
$sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
$sql->execute([$room_id]);
$row = $sql->fetch();
$room_name = $row['classroom_name'];
$floor = $row['classroom_floor'];
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
        <?php
        if (isset($_POST['judge'])) {
            $sql_room = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
            $sql_room->execute([$_SESSION['user']['user_id']]);
            $row_room = $sql_room->fetch();

            $now_time = date("Y/m/d H:i:s");

            if (!$row_room) {
                $sql_insert = $pdo->prepare('INSERT INTO Current_location (user_id, classroom_id, logtime) VALUES (?, ?, ?)');
                $sql_insert->execute([$_SESSION['user']['user_id'], $room_id, $now_time]);
            } else {
                $sql_update = $pdo->prepare('UPDATE Current_location SET classroom_id = ?, logtime = ? WHERE user_id = ?');
                $sql_update->execute([$room_id, $now_time, $_SESSION['user']['user_id']]);
            }
            $list_sql = $pdo->prepare('SELECT * FROM Favorite WHERE follower_id=?');
            $list_sql->execute([$_SESSION['user']['user_id']]);
            $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
            if ($list_raw) {
                foreach ($list_raw as $row) {
                    $info_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE type=? AND user_id=?');
                    $info_sql->execute([
                        $_SESSION['user']['user_id'],
                        $row['follow_id']
                    ]);
                    $info_raw = $info_sql->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }
        ?>

        <h1><?php echo htmlspecialchars($floor); ?>階</h1>
        <span><?php echo htmlspecialchars($room_name); ?></span>

        <form action="room.php?id=<?php echo htmlspecialchars($room_id); ?>" method="post">
            <input type="hidden" name="judge" value="0">
            <input class="room" type="submit" value="位置登録">
        </form>

        <a href="main.php" class="back-link">メインへ</a>
    </main>
</body>

</html>