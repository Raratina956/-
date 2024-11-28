<?php
require 'parts/auto-login.php';
if (isset($_POST['announcement_id'])) {
    $announcement_id = $_POST['announcement_id'];
    $type = 1;
} elseif (isset($_POST['current_location_id'])) {
    $current_location_id = $_POST['current_location_id'];
    $type = 2;
} elseif (isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    $ann_delete = $pdo->prepare('DELETE FROM Announce_check WHERE user_id=? AND $message_id=?');
    $ann_delete -> execute([$_SESSION['user']['user_id'],$message_id]);
    $mess_sql = $pdo->prepare('SELECT * FROM Message WHERE message_id=?');
    $mess_sql ->execute([$message_id]);
    $mess_row = $mess_sql->fetch();
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/chat.php?user_id='.$mess_row['send_id'];
    header("Location: $redirect_url");
    exit();
} else{
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/info.php';
    header("Location: $redirect_url");
    exit();
}
?>

<?php
require 'header.php';
?>
<link rel="stylesheet" href="css/info_detail.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">
