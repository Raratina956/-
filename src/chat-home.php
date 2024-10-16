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

// 最後のメッセージを取得する関数
function getLastMessages($pdo, $user_id) {
    $sql = "
    SELECT m1.send_id, m1.sent_id, m1.message_detail, m1.message_time
    FROM Message m1
    INNER JOIN (
        SELECT 
            LEAST(send_id, sent_id) AS user1,
            GREATEST(send_id, sent_id) AS user2,
            MAX(message_time) AS last_message_time
        FROM Message
        WHERE send_id = :user_id OR sent_id = :user_id
        GROUP BY user1, user2
    ) m2 ON (LEAST(m1.send_id, m1.sent_id) = m2.user1
            AND GREATEST(m1.send_id, m1.sent_id) = m2.user2
            AND m1.message_time = m2.last_message_time)
    ORDER BY m1.message_time DESC";

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
        // 最後のメッセージを取得
        $messages = getLastMessages($pdo, $user_id);
        foreach ($messages as $message): 
            // 相手のIDを特定（自分以外のID）
            $partner_id = ($message['send_id'] == $user_id) ? $message['sent_id'] : $message['send_id']; 
    ?>
        <div class="chat-item">
            <img src="image/<?php echo htmlspecialchars($partner_id); ?>.png" alt="User Image" class="avatar">
            <div class="chat-info">
                <a href="chat.php?user_id=<?php echo htmlspecialchars($partner_id); ?>">
                    <?php echo htmlspecialchars(getUserName($pdo, $partner_id)); ?>
                </a>
                <p>ID: <?php echo htmlspecialchars($partner_id); ?></p>
                <!-- 最後のメッセージを表示 -->
                <p><?php echo htmlspecialchars($message['message_detail']); ?></p>
                <small><?php echo htmlspecialchars($message['message_time']); ?></small>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
