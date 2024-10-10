<?php
require 'parts/auto-login.php';
if (isset($_POST['delete_tag'])) {
    $delete_tag = $_POST['delete_tag'];
    $sql_delete = $pdo->prepare('DELETE FROM Tag_attribute WHERE tag_id=? AND user_id=?');
    $sql_delete->execute([$delete_tag, $_SESSION['user']['user_id']]);
}
?>

<?php
require 'header.php';
?>
<h1>参加タグ一覧</h1>
<?php
$search_sql = $pdo->prepare("SELECT * FROM Tag_attribute WHERE user_id=?");
$search_sql->execute([$_SESSION['user']['user_id']]);
$results = $search_sql->fetchAll(PDO::FETCH_ASSOC);
if ($results) {
    ?>
    <table>
        <th>タグ名</th>
        <th>作成者</th>
        <th></th>
        <?php
        foreach ($results as $row) {
            $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
            $sql_tag->execute([$row['tag_id']]);
            $row_tag = $sql_tag->fetch();
            echo '<tr>';
            echo '<td>', $row_tag['tag_name'], '</td>';
            $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
            $sql_user->execute([$row_tag['user_id']]);
            $row_user = $sql_user->fetch();
            echo '<td>', $row_user['user_name'], '</td>';
            echo '<td>';
            ?>
            <form action="join_tag.php" method="post">
                <input type="hidden" name="delete_tag" value=<?php echo $row['tag_id']; ?>>
                <input type="submit" value="退会">
            </form>
            <?php
            echo '</td>';
            echo '</tr>';
        }
        ?>
    </table>
    <a href="main.php">メインへ</a>
    <?php
} else {
    echo '参加済みのタグがありません';
}
?>