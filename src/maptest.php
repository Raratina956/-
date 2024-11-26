<?php
session_start();

// データベース接続設定
require 'db-connect.php'; // db-connect.php で PDO の接続設定を行っている前提

// 接続を確認し、PDOのインスタンスを作成
try {
    $pdo = new PDO($connect, USER, PASS); // $connect, USER, PASS は db-connect.php で設定されているはず
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

// 他のユーザーの情報と位置情報を取得
$allLocationsStmt = $pdo->query('
    SELECT Icon.user_id, Icon.icon_name, Users.user_name, locations.latitude, locations.longitude 
    FROM Icon
    INNER JOIN Users ON Icon.user_id = Users.user_id
    INNER JOIN locations ON Icon.user_id = locations.user_id
');
$allLocations = $allLocationsStmt->fetchAll(PDO::FETCH_ASSOC);
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
// JavaScript: 他のユーザーの位置情報や友達リストを表示する処理

mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [130.4017, 33.5902],  // 初期位置
    zoom: 12
});

// 他のユーザーの位置情報を取得
const otherUsers = <?php echo json_encode($allLocations); ?>;

function displayFriends(users) {
    const friendList = document.getElementById('friend-list');
    friendList.innerHTML = ''; // 一度リセットしてから追加

    users.forEach(user => {
        const listItem = document.createElement('li');
        listItem.className = 'friend-item';
        
        const userIcon = document.createElement('img');
        userIcon.src = user.icon_name;
        
        const userName = document.createElement('span');
        userName.textContent = user.user_name;

        listItem.appendChild(userIcon);
        listItem.appendChild(userName);

        listItem.addEventListener('click', () => {
            const userPosition = [user.longitude, user.latitude];
            map.flyTo({ center: userPosition, zoom: 15 });

            new mapboxgl.Popup()
                .setLngLat(userPosition)
                .setHTML(`<div>ユーザー名: ${user.user_name}</div>`)
                .addTo(map);
        });

        friendList.appendChild(listItem);
    });
}

displayFriends(otherUsers);

// 友達追加ボタンのクリックイベント
document.getElementById('add-friend-btn').addEventListener('click', () => {
    // 友達申請の処理を呼び出し
});

// 検索バーの入力イベント
document.getElementById('friend-search').addEventListener('input', () => {
    const searchQuery = document.getElementById('friend-search').value.toLowerCase();
    const filteredUsers = otherUsers.filter(user => 
        user.user_name.toLowerCase().includes(searchQuery)
    );
    displayFriends(filteredUsers);
});

// 位置情報を取得し、自分のマーカーを表示
function updateLocation() {
    // 現在地取得とマーカー表示
}

// 位置情報更新ボタンのクリックイベント
document.getElementById('update-location-btn').addEventListener('click', updateLocation);

</script>

</body>
</html>
