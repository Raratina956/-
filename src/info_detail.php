<?php
require 'parts/auto-login.php';
$announcement_id = $_POST['announcement_id'];
$sql_update = $pdo->prepare('UPDATE Announce_check SET read_check = ? WHERE announcement_id = ? AND user_id = ?');
$sql_update->execute([
    1,
    $announcement_id,
    $_SESSION['user']['user_id']
]);
?>

<?php
// require 'header.php';
?>
<?php
$info_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
$info_sql->execute([$announcement_id]);
$info_row = $info_sql->fetch();
$user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
$user_sql->execute([$info_row['send_person']]);
$user_row = $user_sql->fetch();
?>
<h1><?php echo $user_row['user_name']; ?>さんから、アナウンスが来ました</h1>
<h2><?php echo $info_row['content'] ?></h2>
