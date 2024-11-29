<?php

require 'parts/db-connect.php';
// 編集フォームのデータを取得
if (isset($_POST['edit'])) {
    $tag_id = htmlspecialchars($_POST['edit'], ENT_QUOTES, 'UTF-8');

    // 該当するタグのデータを取得
    $query = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id = :tag_id');
    $query->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
    $query->execute();
    $tag = $query->fetch(PDO::FETCH_ASSOC);

    // タグが存在しない場合のエラーハンドリング
    if (!$tag) {
        echo "タグが見つかりませんでした。";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin.css">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

    <title>タグ編集</title>
</head>
<body>

<h1>タグ編集ページ</h1>

<?php echo $tag_id ?>

<?php if ($tag): ?>
    <form method="post" action="admin_tag_edit.php">
        <input type="hidden" name="tag_id" value="<?php echo htmlspecialchars($tag['tag_id'], ENT_QUOTES, 'UTF-8'); ?>">
        <label for="tag_name">タグ名:</label>
        <input type="text" name="tag_name" value="<?php echo htmlspecialchars($tag['tag_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        <button type="submit" name="update">更新</button>
    </form>

    <form method="post" action="admin_tag_edit.php" onsubmit="return confirm('本当に削除しますか？');">
        <input type="hidden" name="tag_id" value="<?php echo htmlspecialchars($tag['tag_id'], ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" name="delete">削除</button>
    </form>
<?php else: ?>
    <p>編集するタグが存在しません。</p>
<?php endif; ?>

</body>
</html>

<?php
// 出力バッファリングを終了してバッファの内容を出力
ob_end_flush();
?>
