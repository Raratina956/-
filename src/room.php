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
?>
<?php
require 'header.php';
?>
<h1><?php echo $floor ?>階</h1>
<span><?php echo $room_name ?></span>
<form action="room.php?id=<?php echo $room_id ?>" method="post">
<input type="submit" value="位置登録">
</form>
