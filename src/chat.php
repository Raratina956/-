<?php
require 'parts/auto-login.php';

// データベース接続設定
try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<p>接続エラー: " . $e->getMessage() . "</p>";
    exit();
}

$partner_id = isset($_GET['partner_id']) ? $_GET['partner_id'] : null;
$logged_in_user_id = isset($_SESSION['user']['user_id']) ? $_SESSION['user']['user_id'] : null;
if (!$partner_id || !$logged_in_user_id) {
    echo "<p>パラメータが不足しています。</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット</title>
    <script>
        let lastMessageTime = ""; // 最後のメッセージの時刻を保持

        // メッセージを取得する関数
        function fetchNewMessages() {
            const partnerId = <?php echo json_encode($partner_id); ?>;
            const xhr = new XMLHttpRequest();
            xhr.open("GET", `get_messages.php?partner_id=${partnerId}&last_message_time=${lastMessageTime}`, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const messages = JSON.parse(xhr.responseText);
                    if (messages.length > 0) {
                        const chatArea = document.getElementById('chat-area');
                        messages.forEach(message => {
                            const messageDiv = document.createElement('div');
                            messageDiv.className = message.send_id === <?php echo json_encode($logged_in_user_id); ?> ? 'person1' : 'person2';
                            messageDiv.innerHTML = `
                                <div class="chat">
                                    <small class="chat-time">${message.message_time}</small>
                                    <span>${message.message_detail}</span>
                                </div>`;
                            chatArea.appendChild(messageDiv);
                        });
                        // 最新メッセージの時刻を更新
                        lastMessageTime = messages[messages.length - 1].message_time;
                        // 最新メッセージにスクロール
                        scrollToLatestMessage();
                    }
                }
            };
            xhr.send();
        }

        // メッセージ送信処理
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('.send-box').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(() => {
                    document.getElementById('textarea').value = ''; // 入力フィールドをクリア
                    fetchNewMessages(); // 送信後に新しいメッセージを取得
                })
                .catch(error => console.error('送信エラー:', error));
            });

            // 一定間隔で新しいメッセージを取得
            setInterval(fetchNewMessages, 5000); // 5秒ごとにリクエスト
        });

        // 最新メッセージにスクロールする関数
        function scrollToLatestMessage() {
            const chatArea = document.getElementById('chat-area');
            chatArea.scrollTop = chatArea.scrollHeight;
        }
    </script>
</head>
<body>
    <div id="chat-area">
        <!-- チャット内容がここに表示される -->
    </div>
    <form class="send-box" action="send_message.php" method="POST">
        <input type="hidden" name="partner_id" value="<?php echo htmlspecialchars($partner_id); ?>">
        <textarea id="textarea" name="message_detail" required></textarea>
        <button type="submit">送信</button>
    </form>
</body>
</html>
