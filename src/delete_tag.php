<?php
require 'parts/auto-login.php';
if (isset($_POST['delete_tag_id'])) {
    $delete_tag_id = $_POST['delete_tag_id'];
}
if (isset($_POST['tag_id'])) {
    $tag_id = $_POST['tag_id'];
    $sql_delete = $pdo->prepare('DELETE FROM Tag_attribute WHERE tag_id=?');
    $sql_delete->execute([$tag_id]);
    $sql_delete = $pdo->prepare('DELETE FROM Tag_list WHERE tag_id=?');
    $sql_delete->execute([$tag_id]);
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
    <link rel="stylesheet" href="css/delete_tag.css">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

</head>
<body>
<a class="back-link" href="javascript:history.back()">戻る</a>
    <main>
        <h1>タグ削除</h1><br>
        <span>削除するタグ内容を確認してください</span><br>
        <table>
            <thead>
                <tr>
                    <th>タグ名</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php
                        $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
                        $sql_tag->execute([$delete_tag_id]);
                        $row_tag = $sql_tag->fetch();
                        echo htmlspecialchars($row_tag['tag_name']);
                        ?>
                    </td>
                    <td>
                        <form action="delete_tag.php" method="post">
                            <input type="hidden" name="tag_id" value="<?php echo htmlspecialchars($delete_tag_id); ?>">
                            <input type="submit"class="button_del" value="削除">
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </main>
</body>
</html>
