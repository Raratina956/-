<?php
require 'parts/auto-login.php';
if (isset($_POST['announcement_id'])) {
    $announcement_id = $_POST['announcement_id'];
    $sql_update = $pdo->prepare('UPDATE Announce_check SET read_check = ? WHERE announcement_id = ? AND user_id = ?');
    $sql_update->execute([
        1,
        $announcement_id,
        $_SESSION['user']['user_id']
    ]);
    $type = 1;
} elseif (isset($_POST['current_location_id'])) {
    $current_location_id = $_POST['current_location_id'];
    $ann_delete = $pdo->prepare('DELETE FROM Announce_check WHERE user_id = ? AND current_location_id = ?');
    $ann_delete->execute([$_SESSION['user']['user_id'], $current_location_id]);
    $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=?');
    $current_sql->execute([$current_location_id]);
    $current_row = $current_sql->fetch();
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/room.php?id=' . $current_row['classroom_id'] . '&update=0';
    header("Location: $redirect_url");
    exit();
} elseif (isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    $ann_delete = $pdo->prepare('DELETE FROM Announce_check WHERE user_id = ? AND message_id = ?');
    $ann_delete->execute([$_SESSION['user']['user_id'], $message_id]);
    $mess_sql = $pdo->prepare('SELECT * FROM Message WHERE message_id=?');
    $mess_sql->execute([$message_id]);
    $mess_row = $mess_sql->fetch();
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/chat.php?user_id=' . $mess_row['send_id'];
    header("Location: $redirect_url");
    exit();
} elseif (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $deltee_sql = $pdo->prepare('DELETE FROM Announce_check WHERE announcement_id = ? AND user_id=?');
    $deltee_sql->execute([$delete_id,$_SESSION['user']['user_id']]);
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/info.php';
    header("Location: $redirect_url");
    exit();
} else {
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
<?php
if ($type == 1) {
    $info_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
    $info_sql->execute([$announcement_id]);
    $info_row = $info_sql->fetch();
    $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
    $user_sql->execute([$info_row['send_person']]);
    $user_row = $user_sql->fetch();
    $tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
    $tag_sql->execute([$info_row['sent_tag']]);
    $tag_row = $tag_sql->fetch();
    if ($user_row) {
        $send_user_name = $user_row['user_name'];
    } else {
        $send_user_name = '対象のユーザーが見つかりません';
    }
    if ($tag_row) {
        $sent_tag_name = $tag_row['tag_name'];
    } else {
        $sent_tag_name = '対象のタグが見つかりません';
    }
    $title = $info_row['title'];
    if (isset($info_row['content'])) {
        $content = $info_row['content'];
    } else {
        $content = '　';
    }
    $sendtime = $info_row['sending_time'];
    $time = timeAgo($sendtime);
}
?>

<div class="content">
    <table>
        <tr>
            <td colspan="2">
                <h3>タイトル</h3>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="border"><?php echo mb_substr($title,0,18); ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <h3>本文</h3>
            </td>
        </tr>
        <tr>
            <td colspan="2"class="border"><?php echo mb_substr($content,0,50); ?></td>
        </tr>
        <tr>
            <td width="50%">送信者</td>
            <td width="50%">受信タグ</td>
        </tr>
        <tr>
            <td width="50%"><?php echo mb_substr($send_user_name,0,10); ?></td>
            <td width="50%"><?php echo mb_substr($sent_tag_name,0,15); ?></td>
        </tr>
        <tr>
            <td width="50%"><?php echo $time; ?></td>
            <td width="50%">
                <form action="info_detail.php" method="post">
                  
                    <input type="hidden" name="delete_id" value=<?php echo $announcement_id; ?>>
                    <input type="submit" value="削除"class="delete">
                </form>
            </td>
        </tr>
    </table>
   <center> <a href="info.php">戻る</a></center>
</div>
</body>
<?php
function timeAgo($logtime)
{
    if (empty($logtime)) {
        return '日時が設定されていません。'; // 空の日時に対する処理
    }
    $now = new DateTime(); // 現在時刻
    $ago = new DateTime($logtime); // 保存された日時
    $diff = $now->diff($ago); // 差分を取得

    // 差分を秒単位で計算
    $diffInSeconds = $now->getTimestamp() - $ago->getTimestamp();

    // 結果の判定
    if ($diffInSeconds < 3600) { // 1時間以内なら
        // 分数で表示
        $minutes = floor($diffInSeconds / 60);
        return $minutes . '分前';
    } elseif ($diffInSeconds < 86400) { // 24時間以内なら
        // 時間数で表示
        $hours = floor($diffInSeconds / 3600);
        return $hours . '時間前';
    } else {
        // 日数で表示
        $days = floor($diffInSeconds / 86400);
        return $days . '日前';
    }
}
?>

</html>