<?php

require 'parts/auto-login.php';
require 'header.php';

try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch(PDOException $e) {
    echo "接続エラー: " . $e->getMessage();
    exit();
}

// URLからuser_idを取得
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// user_idが取得できない場合
if ($user_id === null) {
    echo "user_idが指定されていません。";
    exit();
}


// 最後のメッセージを取得
function getLastMessages($pdo, $user_id) {
    $sql = "
    SELECT DISTINCT m1.send_id, m1.sent_id, m1.message_detail, m1.message_time
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

// ユーザー名を取得
function getUserName($pdo, $user_id) {
    $sql = "SELECT user_name FROM Users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user['user_name'] : '不明';
}

// 未読メッセージ数を取得する関数
function getUnreadMessageCount($pdo, $user_id, $partner_id) {
    $sql = "
    SELECT COUNT(*) AS unread_count
    FROM Message
    WHERE send_id = :partner_id AND sent_id = :user_id AND already = 0";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['unread_count'] : 0;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット</title>
    <link rel="stylesheet" href="mob_css/chat-home-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/chat.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

    <style>
        /* 未読メッセージ数を示す赤丸 */
        .unread-badge {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 0.3em 0.5em;
            font-size: 0.8em;
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .chat-item {
            position: relative;
        }
    </style>
</head>
<body>

<!-- 戻るボタン -->
<div class="back-button">
    <form action="map.php" method="GET">
        <button type="submit" class="back-link">戻る</button>
    </form>
</div>

<!-- 検索フォーム -->
<form action="chat-idCheck.php" class="form" method="post">
    <input type="text" class="textbox" name="search_keyword" placeholder="ユーザーIDまたは名前を入力">
    <button type="submit">検索</button>
</form>

<div class="chat-container">
    <?php 
        // 最後のメッセージを取得
        $messages = getLastMessages($pdo, $user_id);
        foreach ($messages as $message): 
            
            // 相手のIDを特定（自分以外のID）
            $partner_id = ($message['send_id'] == $user_id) ? $message['sent_id'] : $message['send_id'];
            // 未読メッセージ数を取得
            $unread_count = getUnreadMessageCount($pdo, $user_id, $partner_id);

            $iconStmt = $pdo->prepare('select icon_name from Icon where user_id = ?');
            $iconStmt->execute([$partner_id]); 
            $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);

    ?>
        <div class="chat-item">
            <img src="<?php echo $icon['icon_name'] ?>" alt="User Image" class="avatar">
            <div class="chat-info">
                <a href="chat.php?user_id=<?php echo htmlspecialchars($partner_id); ?>">
                    <?php echo  mb_substr(htmlspecialchars(getUserName($pdo, $partner_id)) 0, 10); ?>
                </a>

                <!-- 未読メッセージ数を赤丸で表示 -->
                <?php if ($unread_count > 0): ?>
                    <span class="unread-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
              
                <!-- 最後のメッセージを表示 -->
                <p><?php echo  mb_substr(htmlspecialchars($message['message_detail']),0,20); ?></p>
                <small><?php echo htmlspecialchars($message['message_time']); ?></small>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
