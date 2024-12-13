<?php
require 'parts/auto-login.php';
require 'header.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>アナウンス</title>
    <link rel="stylesheet" href="mob_css/announce-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/announce.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" href="css/announce.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

</head>
<body>
<?php

if (isset($error)) {
    unset($error);
}

if (isset($_POST['title'])) {
    if (!empty($_POST['title'])) {
        $tag_id = $_POST['tag_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $now_time = date("Y/m/d H:i:s");
        $send_user_id = $_SESSION['user']['user_id'];
        $sql_insert = $pdo->prepare('INSERT INTO Notification (send_person,sent_tag,title,content,sending_time) VALUES (?,?,?,?,?)');
        $sql_insert->execute([
            $send_user_id,
            $tag_id,
            $title,
            $content,
            $now_time
        ]);
        $announcement_id = $pdo->lastInsertId();
        $sql_select = $pdo->prepare("SELECT * FROM Tag_attribute WHERE tag_id=?");
        $sql_select->execute([$tag_id]);
        $results = $sql_select->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row_user) {
            $sent_user_id = $row_user['user_id'];
            if ($send_user_id != $sent_user_id) {
                $sql_insert = $pdo->prepare('INSERT INTO Announce_check (announcement_id,user_id,type) VALUES (?,?,?)');
                $sql_insert->execute([
                    $announcement_id,
                    $sent_user_id,
                    1
                ]);
                $sql_insert = $pdo->prepare('INSERT INTO Announce_his(announcement_id,send_person,sent_person) VALUES(?,?,?)');
                $sql_insert->execute([
                    $announcement_id,
                    $_SESSION['user']['user_id'],
                    $sent_user_id
                ]);
            }
        }
        $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
        $sql_tag->execute([$tag_id]);
        $row_tag = $sql_tag->fetch();
        $tag_name = $row_tag['tag_name'];
    } else {
        $error = 'タイトルを入力してください';
    }
}
?>
<main>
<?php
if (empty($_POST['title'])) {
    // 下記アナウンス発信前
    ?>
    <h1 class="okini">アナウンス</h1>
    <?php
    $join_sql = $pdo->prepare("SELECT * FROM Tag_attribute WHERE user_id=?");
    $join_sql->execute([$_SESSION['user']['user_id']]);
    $results = $join_sql->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        ?>
        <form action="announce.php" method="post">
            <select name="tag_id">
                <?php
                foreach ($results as $join_row) {
                    $tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
                    $tag_sql->execute([$join_row['tag_id']]);
                    $tag_row = $tag_sql->fetch(PDO::FETCH_ASSOC);
                    echo '<option value=', $join_row['tag_id'], '>', $tag_row['tag_name'], '</option>';
                }
                ?>
            </select>
            <br>
            <input type="text" name="title" class="title" placeholder="タイトル">
            <?php
            if (isset($error)) {
                echo '<span style="display: block; text-align: center; color: red;">' . $error . '</span>';
            }
            ?>
            <br>
            <textarea name="content"></textarea>
            <br>
            <input type="submit" class="throw" value="送信">
        </form>
        <?php
    } else {
        echo 'タグを追加してください';
    }
    echo '<a href="announce_his.php" class="history" text-align="center">アナウンス履歴</a>';
    echo '<a class="back-link"  href="map.php">戻る</a>';
    // 上記アナウンス発信前
} else {
    // 下記アナウンス発信後
    ?>
    <h2>アナウンス発信しました。</h2>
    <p>
    <h2>宛先：<?php echo mb_substr($tag_name,0,10); ?></h2>
    <p>
    <h2><?php echo mb_substr($title,0,18); ?></h2>
    <h3><?php echo mb_substr($content,0,30); ?></h3>
    <a class="back-link" href="map.php">戻る</a>
    <?php
    // 上記アナウンス発信後
}
?>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const okiniElement = document.querySelector('.okini');
    const text = okiniElement.textContent.trim(); // 空白を削除
    okiniElement.innerHTML = '';

    for (let i = 0; i < text.length; i++) {
        const span = document.createElement('span');
        span.textContent = text[i];
        span.style.animationDelay = `${i * 0.5}s`; // 0.5秒ごとに遅延を設定
        okiniElement.appendChild(span);
    }
});
</script>

</body>
</html>
