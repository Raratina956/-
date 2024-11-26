<?php
session_start();
require 'db-connect.php';
try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

$partner_id = $_SESSION['user']['user_id'];
$iconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id = ?');
$iconStmt->execute([$partner_id]);
$icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
$iconUrl = $icon['icon_name'];

// 他のユーザーの情報と位置情報を取得する
$allLocationsStmt = $pdo->query('
    SELECT Icon.user_id, Icon.icon_name, Users.user_name, locations.latitude, locations.longitude 
    FROM Icon
    INNER JOIN Users ON Icon.user_id = Users.user_id
    INNER JOIN locations ON Icon.user_id = locations.user_id
');
$allLocations = $allLocationsStmt->fetchAll(PDO::FETCH_ASSOC);

// 友達申請の送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_friend_request'])) {
    $receiver_id = $_POST['receiver_id'];

    // すでに友達申請が送られていないかチェック
    $checkStmt = $pdo->prepare('
        SELECT COUNT(*) FROM friend_requests
        WHERE sender_id = ? AND receiver_id = ? AND status = "pending"
    ');
    $checkStmt->execute([$partner_id, $receiver_id]);
    $existingRequest = $checkStmt->fetchColumn();

    if ($existingRequest == 0) {
        // 友達申請を送信
        $insertStmt = $pdo->prepare('
            INSERT INTO friend_requests (sender_id, receiver_id, status) 
            VALUES (?, ?, "pending")
        ');
        $insertStmt->execute([$partner_id, $receiver_id]);

        echo "友達申請が送信されました。";
    } else {
        echo "すでに友達申請を送っています。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>現在地にピンを立てる</title>
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
    <link rel="stylesheet" href="css/mapindex.css">
    <style>
        /* サイドバーのスタイル */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #fff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 10; /* サイドバーがマップの上に来るように */
        }

        /* 地図のスタイル */
        #map {
            margin-left: 250px;  /* サイドバーの幅分だけ地図を右にずらす */
            height: 100vh; /* 高さを画面全体に設定 */
        }

        /* マーカーのスタイル */
        .marker {
            width: 40px;
            height: 40px;
            background-size: cover;
            border-radius: 50%;
            cursor: pointer;
        }

        /* 友達申請ボタン */
        .send-request-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        .send-request-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div id="sidebar">
    <h2>友達一覧</h2>
    <button id="add-friend-btn">友達追加</button>
    <div id="search-container" style="display: none;">
        <input type="text" id="friend-search" placeholder="名前で検索">
    </div>
    <ul id="friend-list">
        <!-- 友達リストはここに追加される -->
    </ul>
    <button id="update-location-btn">位置情報を更新</button>
</div>
<div id='map'></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [130.4017, 33.5902],  // 初期位置を福岡市博多区に設定
    zoom: 12  // ズームレベルを調整
});

// 他のユーザーの位置情報を取得
const otherUsers = <?php echo json_encode($allLocations); ?>;

// 友達一覧を作成
const friendList = document.getElementById('friend-list');
const searchContainer = document.getElementById('search-container');
const friendSearchInput = document.getElementById('friend-search');

// 友達一覧を表示する関数
function displayFriends(users) {
    friendList.innerHTML = ''; // 一度リセットしてから追加
    users.forEach(user => {
        const listItem = document.createElement('li');
        listItem.className = 'friend-item';

        // アイコンと名前を表示
        const userIcon = document.createElement('img');
        userIcon.src = user.icon_name; // アイコン画像
        const userName = document.createElement('span');
        userName.textContent = user.user_name; // ユーザー名

        listItem.appendChild(userIcon);
        listItem.appendChild(userName);

        // 友達申請ボタン
        const requestButton = document.createElement('button');
        requestButton.textContent = '友達申請';
        requestButton.className = 'send-request-btn';
        requestButton.addEventListener('click', () => {
            sendFriendRequest(user.user_id); // 友達申請送信
        });

        listItem.appendChild(requestButton);

        friendList.appendChild(listItem);
    });
}

// 友達申請を送信する関数
function sendFriendRequest(receiverId) {
    const formData = new FormData();
    formData.append('send_friend_request', true);
    formData.append('receiver_id', receiverId);

    fetch('maptest.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
    })
    .catch(error => console.error('Error:', error));
}

// 友達検索機能
friendSearchInput.addEventListener('input', function() {
    const searchTerm = friendSearchInput.value.toLowerCase();
    const filteredUsers = otherUsers.filter(user =>
        user.user_name.toLowerCase().includes(searchTerm)
    );
    displayFriends(filteredUsers);
});

// 初期友達一覧を表示
displayFriends(otherUsers);
</script>

</body>
</html>
