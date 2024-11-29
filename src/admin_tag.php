<?php
// 出力バッファリングを開始
ob_start();
// セッションの開始
session_start();

require 'parts/db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続に失敗しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit();
}



// ユーザーデータを取得するクエリ
$query = $pdo->query('SELECT * FROM Tag_list as T left outer join Users as U on T.user_id = U.user_id ORDER BY tag_id ASC');
$data = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mob_css/admin_tag.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/admin_tag.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" type="text/css" href="css/admin_tag.css" media="screen and (min-width: 1280px)">
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
        <th>タグID</th>
        <th>タグ名</th>
        <th>作成者</th>
        
        <th>操作</th>
    </tr>
    <?php foreach ($data as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['tag_id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($user['tag_name'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8'); ?></td>
         
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
