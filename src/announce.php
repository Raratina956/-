<?php
require 'parts/auto-login.php';
if (isset($_POST['content'])) {
    $tag_id = $_POST['tag_id'];
    $content = $_POST['content'];
    $now_time = date("Y/m/d H:i:s");
    $send_user_id = $_SESSION['user']['user_id'];
    $sql_insert = $pdo->prepare('INSERT INTO Notification (send_person,sent_tag,content,sending_time) VALUES (?,?,?,?)');
    $sql_insert->execute([
        $send_user_id,
        $tag_id,
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
            $sql_insert = $pdo->prepare('INSERT INTO Announce_check (announcement_id,user_id) VALUES (?,?)');
            $sql_insert->execute([
                $announcement_id,
                $sent_user_id
            ]);
        }
    }
    $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
    $sql_tag->execute([$tag_id]);
    $row_tag = $sql_tag->fetch();
    $tag_name = $row_tag['tag_name'];
}
?>
<?php
// require 'header.php';
?>
<?php
if (!isset($_POST['content'])) {
    // 下記アナウンス発信前
    ?>
    <h1>アナウンス</h1>
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
            <textarea name="content"></textarea>
            <br>
            <input type="submit" value="送信">
        </form>

        <?php
    } else {
        echo 'タグを追加してください';
    }
    echo '<a href="main.php">メインへ</a>';
    // 上記アナウンス発信前
} else {
    // 下記アナウンス発信後
    ?>
    <h1>アナウンス発信しました。</h1>
    <br>
    <h2>宛先：<?php echo $tag_name; ?></h2>
    <br>
    <h3><?php echo $content; ?></h3>
    <a href="main.php">メインへ</a>
    <?php
    // 上記アナウンス発信後
}
?>