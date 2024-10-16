<?php
session_start(); // セッションを開始

require "db-connect.php";
try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch(PDOException $e){
    echo "接続エラー: " . $e->getMessage();
    exit();  
}

// URLからuser_idを取得
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// user_idが取得できない場合の処理
if ($user_id === null) {
    echo "user_idが指定されていません。";
    exit();
}

// メッセージを取得する関数
function getMessages($pdo, $user_id) {
    $sql = "SELECT send_id, sent_id FROM Message WHERE send_id = :user_id OR sent_id = :user_id ORDER BY message_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ユーザー名を取得する関数
function getUserName($pdo, $user_id) {
    $sql = "SELECT user_name FROM Users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user['user_name'] : '不明';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット</title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>

<!-- 検索フォーム -->
<form action="chat-idCheck.php" method="post">
    <input type="text" name="search_keyword" placeholder="ユーザーIDまたは名前を入力">
    <button type="submit">検索</button>
</form>

<div class="chat-container">
    <?php 
        $messages = getMessages($pdo, $user_id);
        $partner_ids = []; // 相手のIDを保存する配列

        foreach ($messages as $message) {
            // 相手のIDを取得（自分以外のIDを選択）
            if ($message['send_id'] != $user_id) {
                $partner_ids[] = $message['send_id']; 
            } elseif ($message['sent_id'] != $user_id) {
                $partner_ids[] = $message['sent_id']; 
            }
        }

        // トーク相手の名前とIDをリンクとして表示
        foreach (array_unique($partner_ids) as $partner_id): ?>
            <div class="chat-item">
                <img src="image/<?php echo htmlspecialchars($partner_id); ?>.png" alt="User Image" class="avatar">
                <div class="chat-info">
                    <a href="chat.php?user_id=<?php echo htmlspecialchars($partner_id); ?>">
                        <?php echo htmlspecialchars(getUserName($pdo, $partner_id)); ?>
                    </a>
                    <p>ID: <?php echo htmlspecialchars($partner_id); ?></p>
                </div>
            </div>
    <?php endforeach; ?>
</div>

</body>
</html>
