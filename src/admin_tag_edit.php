<?php
require 'parts/db-connect.php';

// 初期化
$tag = null;
$tag_id = null;

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

// 更新処理
if (isset($_POST['update'])) {
    $tag_id = htmlspecialchars($_POST['tag_id'], ENT_QUOTES, 'UTF-8');
    $tag_name = htmlspecialchars($_POST['tag_name'], ENT_QUOTES, 'UTF-8');

    try {
        $update_query = $pdo->prepare('UPDATE Tag_list SET tag_name = :tag_name WHERE tag_id = :tag_id');
        $update_query->bindParam(':tag_name', $tag_name, PDO::PARAM_STR);
        $update_query->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
        $update_query->execute();

        // 更新が成功した場合のリダイレクト
        header('Location: admin_tag.php');
        exit();
    } catch (PDOException $e) {
        echo '更新中にエラーが発生しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        exit();
    }
}

// 削除処理
if (isset($_POST['delete'])) {
    $tag_id = htmlspecialchars($_POST['tag_id'], ENT_QUOTES, 'UTF-8');

    try {
        $delete_query = $pdo->prepare('DELETE FROM Tag_list WHERE tag_id = :tag_id');
        $delete_query->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
        $delete_query->execute();

        // 削除が成功した場合のリダイレクト
        header('Location: admin_tag.php');
        exit();
    } catch (PDOException $e) {
        echo '削除中にエラーが発生しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
