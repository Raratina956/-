<?php
require 'parts/auto-login.php';
if (isset($_POST['tag_name'])) {
    $tag_name = $_POST['tag_name'];
    $sql_insert = $pdo->prepare('INSERT INTO Tag_list (tag_name,user_id) VALUES (?,?)');
    $sql_insert->execute([
        $tag_name,
        $_SESSION['user']['user_id']
    ]);
}
?>

<?php
// require 'header.php';
?>
<h1>ｍｙタグ一覧</h1>
<h2>タグ作成</h2>
<form action="my_tag.php" method="post">
    タグ名：
    <input type="name" name="tag_name">
    <input type="submit" value="追加">
</form>
<?php
$list_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE user_id=?');
$list_sql->execute([$_SESSION['user']['user_id']]);
$list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
if ($list_raw) {
    ?>
    <table>
        <th>タグID</th>
        <th>タグ名</th>
        <th></th>
        <th></th>
        <?php
        foreach ($list_raw as $row) {
            echo '<tr>';
            echo '<td>', $row['tag_id'], '</td>';
            echo '<td>', $row['tag_name'], '</td>';
            ?>
            <form action="tag_update.php" method="post">
                <input type="hidden" name="tag_id" value=<?php echo $row['tag_id']; ?>>
                <td><input type="submit" value="更新"></td>
            </form>
            <form action="my_tag.php" method="post">
                <input type="hidden" name="tag_id" value=<?php echo $row['tag_id']; ?>>
                <td><input type="submit" value="削除"></td>
            </form>
            <?php
            echo '</tr>';
        }
        ?>
    </table>
    <?php
} else {
    echo '作成されたタグがありません';
}
?>
<a href="main.php">メインへ</a>