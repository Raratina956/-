<?php
require 'parts/auto-login.php';

?>
<?php
// require 'header.php';
?>
<h1>アナウンス</h1>
<?php
$join_sql = $pdo->prepare("SELECT * FROM Tag_attribute WHERE user_id=?");
$join_sql->execute([$_SESSION['user']['user_id']]);
$results = $join_sql->fetchAll(PDO::FETCH_ASSOC);
if ($results) {
    ?>
    <form action="announce.php" method="post">
        <select name="tag">
            <?php
            foreach ($results as $join_row) {
                $tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
                $tag_sql->execute([$join_row['tag_id']]);
                $tag_row = $tag_sql->fetch(PDO::FETCH_ASSOC);
                echo '<option name=',$join_row['tag_id'],'>',$tag_row['tag_name'],'</option>';             
            }
            ?>
        </select>
        <input type="textarea" name="content">
        <input type="submit" value="送信">
    </form>

    <?php
} else {
    echo 'タグを追加してください';
}
?>