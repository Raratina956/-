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
    $now_time = date("Y/m/d H:i:s");
    $sql_update = $pdo->prepare('INSERT INTO Current_location (user_id,classroom_id,logtime) VALUES (?,?,?)');
    $sql_update->execute([
        $_SESSION['user']['user_id'],
        $room_id,
        $now_time
    ]);
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