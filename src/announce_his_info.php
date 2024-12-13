<?php
require 'parts/auto-login.php';
if (isset($_POST['announcement_id'])) {
    $announcement_id = $_POST['announcement_id'];
} else {
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/announce_his.php';
    header("Location: $redirect_url");
    exit();
}
$ann_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
$ann_sql->execute([$announcement_id]);
$ann_row = $ann_sql->fetch(PDO::FETCH_ASSOC);
$send_user_id = $ann_row['send_person'];
$sent_tag_id = $ann_row['sent_tag'];
$title = $ann_row['title'];
$content = $ann_row['content'];
$send_time = $ann_row['sending_time'];
$user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
$user_sql->execute([$send_user_id]);
$user_row = $user_sql->fetch(PDO::FETCH_ASSOC);
$tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
$tag_sql->execute([$sent_tag_id]);
$tag_row = $tag_sql->fetch(PDO::FETCH_ASSOC);
$send_user_name = $user_row['user_name'];
if($tag_row){
    $sent_tag_name = $tag_row['tag_name'];
}else{
    $sent_tag_name = "削除されたタグです";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mob_css/announce_his_info-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/announce_his_info.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" type="text/css" href="css/announce_his_info.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

</head>
<body>
    <?php
        require 'header.php';
    ?>
    <h1 style="text-align: center;"><sapn>アナウンス詳細</sapn></h1><br>
    <div class="container">
        <div class="announcement-item">
            <span class="label">タイトル：</span>
            <span class="content"><?php echo mb_substr(htmlspecialchars($title),0,18); ?></span>
        </div>
        <div class="announcement-item">
            <span class="label">内容：</span>
            <span class="content"><?php echo mb_substr(nl2br(htmlspecialchars($content)),0,30); ?></span>
        </div>
        <div class="announcement-item">
            <span class="label">投稿主：</span>
            <span class="content"><?php echo hmb_substr(tmlspecialchars($send_user_name),0,10); ?></span>
        </div>
        <div class="announcement-item">
            <span class="label">宛先タグ：</span>
            <span class="content"><?php echo mb_substr(htmlspecialchars($sent_tag_name),0,10); ?></span>
        </div>
        <div class="announcement-item">
            <span class="label">日時：</span>
            <span class="content"><?php echo htmlspecialchars($send_time); ?></span>
        </div>
    </div>
    <?php echo '<form action="announce_his.php?user_id=', $_SESSION['user']['user_id'], '" method="post">' ?>
        <input type="submit" name="back-btn" class="back-btn" value="戻る">
    </form>
</body>
</html>