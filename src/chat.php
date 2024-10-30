<?php
session_start(); // セッションを開始

require "db-connect.php";
try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch(PDOException $e) {
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
function getMessages($pdo, $logged_in_user_id, $partner_id) {
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
$iconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id = ?');
$iconStmt->execute([$partner_id]);
$icon = $iconStmt->fetch(PDO::FETCH_ASSOC);

// 相手の情報を取得
$sql = "SELECT user_name FROM Users WHERE user_id = :partner_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
$stmt->execute();
$partner = $stmt->fetch(PDO::FETCH_ASSOC);


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
        echo 'success';
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
    <link rel="stylesheet" href="css/chat2.css">
    <link rel="stylesheet" href="css/test.css">
</head>
<body>
<div class="chat-system">
    <div class="chat-box">


        <!-- 相手のアイコンと名前表示部分 -->
        <div class="chat-header">
        <img src="<?php echo $icon['icon_name']; ?>"  ?>
            <span class="partner-name"><?php echo htmlspecialchars($partner['user_name']); ?></span>

            <div class="slide-menu">
        <!-- メニューリスト -->
            <?php
                //ユーザー情報を持ってくる
                    $users=$pdo->prepare('select * from Users where user_id=?');
                    // $users->execute([$_SESSION['user']['user_id']]);
                    $users->execute([$_SESSION['user']['user_id']]);
                    
                    //アイコン情報を持ってくる
                    $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
                    $iconStmt->execute([$_SESSION['user']['user_id']]);
                    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);

                    echo '<ul>';
                    //DBから持ってきたユーザー情報を「$user」に入れる
                        foreach($users as $user){
                            echo '<li><img src="', $icon['icon_name'], '" width="50%" height="50%" class="usericon2"></li>';
                            echo '<li>',$user['user_name'],'</li>';

                        }

                ?>
                <form action="search.php" method="post">
                    <input type="text" name="search" class="tbox">
                    <input type="submit" class="search1" value="検索">
                </form>

            <li><a href="map.php">MAP</a></li>
            <?php echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '">自分のプロフィール</a></li>'; ?>
            <li><a href="favorite.php">お気に入り</a></li>
            <li><a href="qr_read.php">QRカメラ</a></li>
            <?php echo '<li><a href="chat-home.php?user_id=', $_SESSION['user']['user_id'], '">チャット</a></li>'; ?>
            <li><a href="tag_list.php">みんなのタグ</a></li>
            <li><a href="my_tag.php">MYタグ</a></li>
            <li><a href="announce.php">アナウンス</a></li>
            <!-- 以下ログアウト -->
            <form id="myForm" action="" method="post">
                <input type="hidden" name="logout" value="1">
            </form>
            <li><a href="#" id="submitLink">ログアウト</a></li>
          
            <script>
                document.getElementById('submitLink').addEventListener('click', function (event) {
                    event.preventDefault(); // リンクのデフォルトの動作を防止
                    // 現在のURLを取得
                    var currentUrl = window.location.href;
                    // フォームのactionに現在のURLを設定
                    document.getElementById('myForm').action = currentUrl;
                    // フォームを送信
                    document.getElementById('myForm').submit();
                });
            </script>

            <!-- 以上ログアウト -->
        </ul>
    </div>
        </div>

        <!-- 広告バナー -->
        <!-- <div class="ad-banner" id="ad-banner">
            <a href="https://aso2201195.boo.jp/zonotown/top.php" target="_blank">
                <img src="image/banner.png" alt="広告バナー" class="ad-image">
            </a>
        </div> -->

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
            <!-- トップページに戻るボタン -->
            <form action="chat-home.php" method="GET" class="back-form">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user']['user_id']); ?>">
                <input class="btn back-btn" type="submit" value="Top">
            </form>

            <!-- メッセージ送信フォーム -->
            <form class="send-box flex-box" 
                action="chat.php?user_id=<?php echo htmlspecialchars($partner_id); ?>#chat-area" 
                method="post">
                <textarea id="textarea" name="text" rows="1" required placeholder="message.."></textarea>
                <input type="submit" name="sub" value="送信" id="send-btn">
            </form>
        </div>

    </div>
</div>
<script>
    function scrollToLatestMessage() {
    const latestMessage = document.getElementById('latest-message');
    latestMessage.scrollIntoView({ behavior: 'smooth', block: 'end' }); // オプションに 'block: end' を追加
    }
    document.getElementById('send-btn').addEventListener('click', function (e) {
        // e.preventDefault(); // この行を削除
        scrollToLatestMessage(); // クリック時に最新メッセージへスクロール
    });


    function adjustChatAreaHeight() {
    const chatArea = document.getElementById('chat-area');
    const chatBox = document.querySelector('.chat-box');
    const sendContainer = document.querySelector('.send-container');
    const header = document.querySelector('.chat-header');

    // チャットエリアの高さを再計算
    const availableHeight = window.innerHeight 
        - header.offsetHeight 
        - sendContainer.offsetHeight;

    chatArea.style.height = `${availableHeight}px`;
    }

    // ページロード時とリサイズ時にチャットエリアの高さを調整
    window.onload = adjustChatAreaHeight;
    window.onresize = adjustChatAreaHeight;
</script>
</body>
</html>
