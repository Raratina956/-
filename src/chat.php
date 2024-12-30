<?php
require 'parts/auto-login.php';
require 'header.php';

try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "接続エラー: " . $e->getMessage();
    exit();
}

// URLから相手のuser_idを取得
$partner_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// ログイン中のユーザーIDをセッションから取得
$logged_in_user_id = isset($_SESSION['user']['user_id']) ? $_SESSION['user']['user_id'] : null;

// user_idが取得できない場合の処理
if ($partner_id === null || $logged_in_user_id === null) {
    echo "ユーザーIDが指定されていません。";
    exit();
}

// メッセージを取得し既読フラグを更新する関数
function getMessages($pdo, $logged_in_user_id, $partner_id)
{
    // メッセージを取得するSQL
    $sql = "SELECT message_id, send_id, sent_id, message_detail, message_time 
            FROM Message 
            WHERE (send_id = :logged_in_user_id AND sent_id = :partner_id)
               OR (send_id = :partner_id AND sent_id = :logged_in_user_id)
            ORDER BY message_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':logged_in_user_id', $logged_in_user_id, PDO::PARAM_INT);
    $stmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 受信したメッセージ（自分宛て）の既読フラグを更新
    $sql_update = "UPDATE Message SET already = 1 
                   WHERE sent_id = :logged_in_user_id AND send_id = :partner_id AND already = 0";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':logged_in_user_id', $logged_in_user_id, PDO::PARAM_INT);
    $stmt_update->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
    $stmt_update->execute();

    return $messages;
}

// アイコンを取得する処理
$iconStmt = $pdo->prepare('select icon_name from Icon where user_id = ?');
$iconStmt->execute([$partner_id]);
$iconchat = $iconStmt->fetch(PDO::FETCH_ASSOC);

// 相手の情報を取得
$sql = "SELECT user_name FROM Users WHERE user_id = :partner_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
$stmt->execute();
$partner = $stmt->fetch(PDO::FETCH_ASSOC);

// info既読機能
$send_id_che = $partner_id;
$sent_id_che = $logged_in_user_id;
$mess_sql = $pdo->prepare('SELECT * FROM Message WHERE send_id = ? AND sent_id=?');
$mess_sql->execute([$send_id_che, $sent_id_che]);
$mess_row = $mess_sql->fetchAll(PDO::FETCH_ASSOC);
if ($mess_row) {
    foreach ($mess_row as $mess_list) {
        $message_id_check = $mess_list['message_id'];
        $mess_check = $pdo->prepare('SELECT * FROM Announce_check WHERE message_id=?');
        $mess_check->execute([$message_id_check]);
        $mess_check_row = $mess_check->fetch();
        if ($mess_check_row) {
            $info_up = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE message_id=?');
            $info_up->execute([1, $message_id_check]);
        }
    }
}

// メッセージ送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $send_id = $logged_in_user_id; // セッションから送信者のIDを取得
    $sent_id = $partner_id; // 受信者のIDはリンクから取得した相手のID
    $message_detail = $_POST['text'];
    $message_time = date('Y/m/d H:i:s');

    $sql = "INSERT INTO Message (send_id, sent_id, message_detail, message_time, already) 
            VALUES (:send_id, :sent_id, :message_detail, :message_time, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':send_id', $send_id);
    $stmt->bindParam(':sent_id', $sent_id);
    $stmt->bindParam(':message_detail', $message_detail);
    $stmt->bindParam(':message_time', $message_time);

    if ($stmt->execute()) {
        // info 追加
        $message_id = $pdo->lastInsertId();
        $ann_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id = ? AND type=?');
        $ann_sql->execute([$sent_id, 3]);
        $ann_row = $ann_sql->fetchAll(PDO::FETCH_ASSOC);
        $found = false;
        if ($ann_row) {
            foreach ($ann_row as $ann_list) {
                $send_id = intval($send_id);
                $sent_id = intval($sent_id);
                $message_id_check = $ann_list['message_id'];
                $mess_sql = $pdo->prepare('SELECT * FROM Message WHERE message_id=?');
                $mess_sql->execute([$message_id_check]);
                $mess_row = $mess_sql->fetch(PDO::FETCH_ASSOC);
                $send_id_check = $mess_row['send_id'];
                $sent_id_check = $mess_row['sent_id'];
                if ($send_id == $send_id_check && $sent_id == $sent_id_check) {
                    $info_up = $pdo->prepare('UPDATE Announce_check SET read_check = ?, message_id=? WHERE message_id=?');
                    $info_up->execute([0, $message_id, $message_id_check]);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $info_insert = $pdo->prepare('INSERT INTO Announce_check(message_id, user_id, read_check, type) VALUES (?, ?, ?, ?)');
                $info_insert->execute([$message_id, $sent_id, 0, 3]);
            }
        } else {
            $info_insert = $pdo->prepare('INSERT INTO Announce_check(message_id,user_id,read_check,type) VALUES (?,?,?,?)');
            $info_insert->execute([$message_id, $sent_id, 0, 3]);
        }
    } else {
        $error_info = $stmt->errorInfo();
        echo "登録に失敗しました: " . $error_info[2];
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット</title>
    <link rel="stylesheet" href="mob_css/chat-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/chat2.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" href="css/chat2.css" media="screen and (min-width: 1280px)">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">
</head>

<body>
    <div class="chat-system">
        <div class="chat-box">
            <!-- 相手のアイコンと名前表示部分 -->
            <div class="chat-header">
                <form action="chat-home.php?user_id=<?php echo $_SESSION['user']['user_id']; ?>" method="post"
                    class="backform">
                    <input type="submit" name="back-btn" class="back-btn" value="戻る">
                </form>
                <div class="center-content">
                    <img src="<?php echo htmlspecialchars($iconchat['icon_name']); ?>" alt="Partner Icon">
                    <span class="partner-name"><?php echo htmlspecialchars($partner['user_name']); ?></span>
                </div>
            </div>

            <!-- 広告バナー -->
            <!--
        <div class="ad-banner" id="ad-banner">
            <a href="https://aso2201195.boo.jp/zonotown/top.php" target="_blank">
                <img src="image/banner.png" alt="広告バナー" class="ad-image">
            </a>
        </div>
        -->

            <div class="chat-area" id="chat-area">
                <?php
                // 指定した相手とのチャット履歴を取得して表示
                $messages = getMessages($pdo, $logged_in_user_id, $partner_id);
                foreach ($messages as $message): ?>
                    <?php $class = ($message['send_id'] == $logged_in_user_id) ? 'person1' : 'person2'; ?>
                    <div class="<?php echo $class; ?>">
                        <div class="chat">
                            <small class="chat-time"><?php echo htmlspecialchars($message['message_time']); ?></small>
                            <span><?php echo htmlspecialchars($message['message_detail']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div id="latest-message"></div>
            </div>
            <div class="send-container">
                <!-- メッセージ送信フォーム -->
                <form class="send-box flex-box"
                    action="chat.php?user_id=<?php echo htmlspecialchars($partner_id); ?>#latest-message" method="post">
                    <textarea id="textarea" name="text" rows="1" required placeholder="message.."></textarea>
                    <input type="submit" name="sub" class="send" value="送信" id="send-btn">
                </form>
            </div>
        </div>
    </div>

    <script>
        let lastMessageTime = "<?php echo end($messages)['message_time'] ?? '1970-01-01 00:00:00'; ?>";

        function fetchNewMessages() {
            const partnerId = "<?php echo htmlspecialchars($partner_id); ?>";

            fetch(`fetch_new_messages.php?user_id=${partnerId}&last_message_time=${encodeURIComponent(lastMessageTime)}`)
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data)) {
                        data.forEach(message => {
                            const className = message.send_id === "<?php echo $logged_in_user_id; ?>" ? 'person1' : 'person2';
                            const chatArea = document.getElementById('chat-area');
                            const newMessage = document.createElement('div');
                            newMessage.className = className;
                            newMessage.innerHTML = `
                            <div class="chat">
                                <small class="chat-time">${message.message_time}</small>
                                <span>${message.message_detail}</span>
                            </div>
                        `;
                            chatArea.appendChild(newMessage);
                            lastMessageTime = message.message_time; // 最新のメッセージ時間を更新
                        });
                        scrollToLatestMessage();
                    }
                })
                .catch(error => console.error('エラー:', error));
        }

        setInterval(fetchNewMessages, 5000); // 5秒ごとに新しいメッセージを取得
    </script>

    <script>
        // function scrollToLatestMessage() {
        //     const latestMessage = document.getElementById('latest-message');
        //     latestMessage.scrollIntoView({ behavior: 'smooth', block: 'end' }); // オプションに 'block: end' を追加
        //     }
        // document.getElementById('send-btn').addEventListener('click', function (e) {
        //     // e.preventDefault(); // この行を削除
        //     scrollToLatestMessage(); // クリック時に最新メッセージへスクロール
        // });


        // function adjustChatAreaHeight() {
        // const chatArea = document.getElementById('chat-area');
        // const chatBox = document.querySelector('.chat-box');
        // const sendContainer = document.querySelector('.send-container');
        // const header = document.querySelector('.chat-header');

        // // チャットエリアの高さを再計算
        // const availableHeight = window.innerHeight
        //     - header.offsetHeight
        //     - sendContainer.offsetHeight;

        // chatArea.style.height = `${availableHeight}px`;
        // }

        // // ページロード時とリサイズ時にチャットエリアの高さを調整
        // window.onload = adjustChatAreaHeight;
        // window.onresize = adjustChatAreaHeight;

    </script>
    <script type="text/javascript" src="js/chat.js" async></script>
</body>

</html>