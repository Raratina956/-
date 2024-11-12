<?php
require 'parts/auto-login.php';
$tag_id = $_POST['tag_id'];
$sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
$sql->execute([$tag_id]);
$row = $sql->fetch(PDO::FETCH_ASSOC);
$tag_name = $row['tag_name'];
if (isset($_POST['up_tag_name'])) {
    $up_tag_name = $_POST['up_tag_name'];
    $sql_update = $pdo->prepare('UPDATE Tag_list SET tag_name = ? WHERE tag_id = ?');
    $sql_update->execute([
        $up_tag_name,
        $tag_id
    ]);
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/my_tag.php';
    header("Location: $redirect_url");
    exit();
}
?>

<?php
require 'header.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タグ更新</title>
    <link rel="stylesheet" href="css/tag_update.css"> <!-- Link to the CSS file -->
</head>
<body>
    <main>
        <h1>タグ更新</h1>
        <span>更新内容を入力してください</span>
        <table>
            <tr>
                <th>タグ名</th>
                <th></th>
            </tr>
            <tr>
                <form action="tag_update.php" method="post">
                    <input type="hidden" name="tag_id" value="<?php echo $tag_id; ?>">
                    <td><input class="text" type="text" name="up_tag_name" value="<?php echo $tag_name; ?>"></td>
                    <td><input type="submit" class="button_up" value="更新"></td>
                </form>
            </tr>
        </table>
        <a href="my_tag.php"class="back-link">戻る</a>
    </main>
</body>
</html>