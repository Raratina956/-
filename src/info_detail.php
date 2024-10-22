<?php
require 'parts/auto-login.php';
if (isset($_POST['announcement_id'])) {
    $announcement_id = $_POST['announcement_id'];
    $type = 1;
} elseif ($_POST['current_location_id']) {
    $current_location_id = $_POST['current_location_id'];
    $type = 2;
}
switch ($type) {
    case 1:
        $sql_update = $pdo->prepare('UPDATE Announce_check SET read_check = ? WHERE announcement_id = ? AND user_id = ?');
        $sql_update->execute([
            1,
            $announcement_id,
            $_SESSION['user']['user_id']
        ]);
        if (isset($_POST['read'])) {
            $sql_update = $pdo->prepare('UPDATE Announce_check SET read_check = ? WHERE announcement_id = ? AND user_id = ?');
            $sql_update->execute([
                0,
                $announcement_id,
                $_SESSION['user']['user_id']
            ]);
        }
        if (isset($_POST['delete'])) {
            $sql_delete = $pdo->prepare('DELETE FROM Announce_check WHERE announcement_id = ? AND user_id=?');
            $sql_delete->execute([
                $announcement_id,
                $_SESSION['user']['user_id']
            ]);
            $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/info.php';
            header("Location: $redirect_url");
            exit();
        }
        break;

    default:
        # code...
        break;
}
?>

<?php
require 'header.php';
?>
<link rel="stylesheet" href="css/info_detail.css">
<?php
switch ($type) {
    case 1:
        $info_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
        $info_sql->execute([$announcement_id]);
        $info_row = $info_sql->fetch();
        $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
        $user_sql->execute([$info_row['send_person']]);
        $user_row = $user_sql->fetch();
        ?>
        <div class="content">
            <h1><?php echo $user_row['user_name']; ?>さんから、アナウンスが来ました</h1>
            <h2><?php echo $info_row['content'] ?></h2>
            <p>
                <span>未読にする</span>
            <form action="info_detail.php" method="post">
                <input type="hidden" name="announcement_id" value=<?php echo $announcement_id; ?>>
                <input type="hidden" name="read" value="0">
                <input type="submit" value="変更" class="change-btn">
            </form>
            </p>
            <p>
                <!-- <span>削除</span> -->
            <form action="info_detail.php" method="post">
                <input type="hidden" name="announcement_id" value=<?php echo $announcement_id; ?>>
                <input type="hidden" name="delete" value="0">
                <input type="submit" value="削除" class="delete-btn">
            </form>
            </p>
            <a href="info.php">戻る</a>
        </div>
        <?php
        break;
    case 2:
        $info_sql = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=?');
        $info_sql->execute([$current_location_id]);
        $info_row = $info_sql->fetch();
        $send_id = $info_row['user_id'];
        $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
        $user_sql->execute([$send_id]);
        $user_row = $user_sql->fetch();
        $send_name = $user_row['user_name'];
        $logtime = $info_row['logtime'];
        ?>
        <div class="content">
            <p>
                <span>お気に入りに登録している<?php echo $send_name; ?>さんが位置情報を更新しました</span>
            </p>
            <a href="info.php">戻る</a>
        </div>
        <?php
        break;
    default:
        # code...
        break;
}
