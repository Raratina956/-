<?php
require 'parts/auto-login.php';
if (isset($_POST['tag_name'])) {
    $tag_name = $_POST['tag_name'];
    $sql_insert = $pdo->prepare('INSERT INTO Tag_list (tag_name) VALUES (?)');
    $sql_insert->execute([$tag_name]);
}
?>

<?php
require 'header.php';
?>
<h1>タグ作成</h1>
<form action="tag_create.php" method="post">
    タグ名
    <input type="name" name="tag_name">
    <input type="submit" value="作成">
</form>
<a href="main.php">メインへ</a>