<?php
require 'parts/auto-login.php';
?>
<?php
$room_id = $_GET['id'];
$sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
$sql->execute([$room_id]);
$row = $sql->fetch();
$room_name = $row['classroom_name'];
$floor = $row['classroom_floor'];
if (isset($_POST['judge'])) {
    $sql_room = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
    $sql_room->execute([$_SESSION['user']['user_id']]);
    $row_room = $sql_romm->fetch();
    if (!$row_romm) {
        $now_time = date("Y/m/d H:i:s");
        $sql_insert = $pdo->prepare('INSERT INTO Current_location (user_id,classroom_id,logtime) VALUES (?,?,?)');
        $sql_insert->execute([
            $_SESSION['user']['user_id'],
            $room_id,
            $now_time
        ]);
    } else {
        $now_time = date("Y/m/d H:i:s");
        $sql_update = $pdo->prepare('UPDATE Current_location SET classroom_id = ? , logtime = ? WHERE user_id = ?');
        $sql_update->execute([
            $room_id,
            $now_time,
            $_SESSION['user']['user_id']
        ]);
    }
}
?>
<?php
require 'header.php';
?>
<h1><?php echo $floor ?>階</h1>
<span><?php echo $room_name ?></span>
<form action="room.php?id=<?php echo $room_id ?>" method="post">
    <input type="hidden" name="judge" value="0">
    <input type="submit" value="位置登録">
</form>