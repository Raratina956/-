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



// ユーザーデータを取得するクエリ
$query = $pdo->query('SELECT * FROM Classroom ORDER BY classroom_id ASC');
$data = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin.css">
    <title>管理者ページ</title>
</head>
<body>

<h1>管理者ページ</h1>

<table border="1">
    <tr>
        <th>教室ID</th>
        <th>教室名</th>
        <th>階層</th>
        
        <th>操作</th>
    </tr>
    <?php foreach ($data as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['classroom_id'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($user['classroom_name'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($user['classroom_floor'], ENT_QUOTES, 'UTF-8'); ?></td>
         
            
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>

<?php
// 出力バッファリングを終了してバッファの内容を出力
ob_end_flush();
?>
