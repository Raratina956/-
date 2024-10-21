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
                $current_location_id = $pdo->lastInsertId();
            } else {
                $sql_update = $pdo->prepare('UPDATE Current_location SET classroom_id = ?, logtime = ? WHERE user_id = ?');
                $sql_update->execute([$room_id, $now_time, $_SESSION['user']['user_id']]);
                $sql_current = $pdo->prepare('SELECT * FROM Current_location WHERE classroom_id=? AND user_id=?');
                $sql_current->execute([$room_id, $_SESSION['user']['user_id']]);
                $row_current = $sql_current->fetch();
                $current_location_id = $row_current['current_location_id'];
            }
            $list_sql = $pdo->prepare('SELECT * FROM Favorite WHERE follower_id=?');
            $list_sql->execute([$_SESSION['user']['user_id']]);
            $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
            if ($list_raw) {
                foreach ($list_raw as $list_row) {
                    $info_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE type=? AND user_id=?');
                    $info_sql->execute([
                        $_SESSION['user']['user_id'],
                        $list_row['follow_id']
                    ]);
                    $info_raw = $info_sql->fetchAll(PDO::FETCH_ASSOC);
                    if ($info_raw) {
                        foreach ($info_raw as $info_row) {
                            $sql_update = $pdo->prepare('UPDATE Announce_check SET current_location_id = ?, WHERE user_id = ? AND type=?');
                            $sql_update->execute([
                                $current_location_id,
                                $list_row['follow_id'],
                                2
                            ]);
                        }
                    } else {
                        $sql_insert = $pdo->prepare('INSERT INTO Announce_check (current_location_id,user_id,type) VALUES (?,?,?)');
                        $sql_insert->execute([
                            $current_location_id,
                            $list_row['follow_id'],
                            2
                        ]);
                    }
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