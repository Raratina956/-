<?php
session_start();
require 'db-connect.php';
if(isset($_POST['classroom_id'])){
    $class_id = $_POST['classroom_id'];
}else{
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/admin_room.php';
    header("Location: $redirect_url");
    exit();
}
$class_sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
$class_sql ->execute([$class_id]);
$class_row = $class_sql->fetch();
$class_name = $class_row['classroom_name'];
$class_floor = $class_row['classroom_floor'];
?>