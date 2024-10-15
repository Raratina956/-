<?php
require 'header.php'; // Your header file
?>
<main>
    <?php
    if (!isset($_POST['content'])) {
        // Announcement form before submission
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
        echo '<a class="back-link" href="main.php">メインへ</a>';
    } else {
        // Announcement after submission
        ?>
        <h1>アナウンス発信しました。</h1>
        <br>
        <h2>宛先：<?php echo $tag_name; ?></h2>
        <br>
        <h3><?php echo $content; ?></h3>
        <a class="back-link" href="main.php">メインへ</a>
        <?php
    }
    ?>
</main>
