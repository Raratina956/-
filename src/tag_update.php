<?php
require 'parts/auto-login.php';
$tag_id = $_POST['tag_id'];
$sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
$sql->execute([$tag_id]);
$row = $sql->fetch(PDO::FETCH_ASSOC);
$tag_name = $row['tag_name'];
if (isset($_POST['up_tag_name'])) {
    $up_tag_name = $_POST['up_tag_name'];
    $sql_update = $pdo->prepare('UPDATE tag_list SET tag_name = ? WHERE tag_id = ?');
    $sql_update->execute([
        $tag_id,
        $_SESSION['user']['user_id']
    ]);
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/my_tag.php';
    header("Location: $redirect_url");
    exit();
}
?>

<?php
// require 'header.php';
?>
<h1>タグ更新</h1>
<span>更新内容を入力してください</span>
<table>
    <tr>
        <th>タグ名</th>
        <th></th>
    </tr>
    <tr>
        <form action="tag_update.php" method="post">
            <td><input type="text" name="up_tag_name" value=""></td>
            <td><input type="submit" value="更新"></td>
        </form>
    </tr>
</table>