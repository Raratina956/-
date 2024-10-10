<?php
require 'parts/auto-login.php';
if (isset($delete_tag_id)) {
    $delete_tag_id = $_POST['delete_tag_id'];
}
if (isset($_POST['tag_id'])) {
    $tag_id = $_POST['tag_id'];
    $sql_delete = $pdo->prepare('DELETE FROM Tag_attribute WHERE tag_id=?');
    $sql_delete->execute([$tag_id]);
    $sql_delete = $pdo->prepare('DELETE FROM Tag_list WHERE tag_id=?');
    $sql_delete->execute([$tag_id]);
}
?>

<?php
require 'header.php';
?>
<h1>タグ削除</h1><br>
<span>削除するタグ内容を確認してください</span><br>
<table>
    <th>タグ名</th>
    <th></th>
    <tr>
        <td>
            <?Php
            $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
            $sql_tag->execute([$delete_tag_id]);
            $row_tag = $sql_user->fetch();
            echo $row_tag['tag_name'];
            ?>
        </td>
        <td>
            <form action="delete_tag.php" method="post">
                <input type="hidden" name="tag_id"value=<?php echo $delete_tag_id;?>>
                <input type="submit" value="削除">
            </form>
        </td>
    </tr>
</table>