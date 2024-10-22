<?php
// 出力バッファリングを開始
ob_start();
// セッションの開始
session_start();

require 'db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続に失敗しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit();
}

// 編集フォームのデータを取得
if (isset($_POST['edit'])) {
    $tag_id = htmlspecialchars($_POST['edit'], ENT_QUOTES, 'UTF-8');

    // 該当するタグのデータを取得
    $query = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id = :tag_id');
    $query->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
    $query->execute();
    $tag = $query->fetch(PDO::FETCH_ASSOC);
}

// 編集内容の保存
if (isset($_POST['update'])) {
    $tag_id = htmlspecialchars($_POST['tag_id'], ENT_QUOTES, 'UTF-8');
    $tag_name = htmlspecialchars($_POST['tag_name'], ENT_QUOTES, 'UTF-8');

    // タグの更新クエリ
    $query = $pdo->prepare('UPDATE Tag_list SET tag_name = :tag_name WHERE tag_id = :tag_id');
    $query->bindParam(':tag_name', $tag_name, PDO::PARAM_STR);
    $query->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
    $query->execute();

    header('Location: admin_page.php');
    exit();
}

// タグの削除
if (isset($_POST['delete'])) {
    $tag_id = htmlspecialchars($_POST['tag_id'], ENT_QUOTES, 'UTF-8');

    // タグの削除クエリ
    $query = $pdo->prepare('DELETE FROM Tag_list WHERE tag_id = :tag_id');
    $query->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
    $query->execute();

    header('Location: admin_page.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin.css">
    <title>タグ編集</title>
</head>
<body>

<h1>タグ編集ページ</h1>

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

</body>
</html>

<?php
// 出力バッファリングを終了してバッファの内容を出力
ob_end_flush();
?>
