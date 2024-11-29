<?php
// 出力バッファリングを開始
ob_start();
// セッションの開始
session_start();

require 'header.php';
require 'parts/db-connect.php';

// 管理者権限チェック
if (!isset($_SESSION['User']['user_id']) || $_SESSION['User']['user_id'] != 5) {
    echo 'アクセス権限がありません。';
    exit();
}

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続に失敗しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit();
}

// 投稿削除処理
if (isset($_POST['delete_post_id'])) {
    $delete_post_id = intval($_POST['delete_post_id']);

    // トランザクション開始
    $pdo->beginTransaction();

    try {
        // user_nice テーブルから関連するデータを削除
        $delete_user_nice_query = $pdo->prepare('DELETE FROM user_nice WHERE post_id = ?');
        $delete_user_nice_query->execute([$delete_post_id]);

        // nice_post テーブルから関連するデータを削除
        $delete_nice_query = $pdo->prepare('DELETE FROM nice_post WHERE post_id = ?');
        $delete_nice_query->execute([$delete_post_id]);

        // tag_map テーブルから関連するデータを削除
        $delete_tag_map_query = $pdo->prepare('DELETE FROM tag_map WHERE post_id = ?');
        $delete_tag_map_query->execute([$delete_post_id]);

        // post テーブルからデータを削除
        $delete_post_query = $pdo->prepare('DELETE FROM post WHERE post_id = ?');
        $delete_post_query->execute([$delete_post_id]);

        // トランザクションコミット
        $pdo->commit();
    } catch (PDOException $e) {
        // エラーが発生した場合はロールバック
        $pdo->rollBack();
        echo '削除に失敗しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// 投稿データを取得するクエリ
$query = $pdo->query('SELECT * FROM post ORDER BY post_day DESC');
$data = $query->fetchAll(PDO::FETCH_ASSOC);
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

    <title>管理者ページ</title>
</head>
<body>

<h1>管理者ページ</h1>

<table border="1">
    <tr>
        <th>投稿ID</th>
        <th>タイトル</th>
        <th>カテゴリーID</th>
        <th>投稿日時</th>
        <th>操作</th>
    </tr>
    <?php foreach ($data as $post): ?>
        <tr>
            <td><?php echo htmlspecialchars($post['post_id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($post['category_id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($post['post_day'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <form method="post" action="admin_tag_edit.php">
                    <input type="hidden" name="edit" value="<?php echo htmlspecialchars($post['post_id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit">編集</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>

<?php
// 出力バッファリングを終了してバッファの内容を出力
ob_end_flush();
?>
