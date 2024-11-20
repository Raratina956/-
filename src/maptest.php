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

// 自分のID
$selfUserId = 7;

// 自分のアイコン取得
$selfIconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id = ?');
$selfIconStmt->execute([$selfUserId]);
$selfIcon = $selfIconStmt->fetch(PDO::FETCH_ASSOC);

// 他のユーザーの情報を取得
$friendStmt = $pdo->query('
    SELECT 
        Icon.user_id, 
        Icon.icon_name, 
        locations.latitude, 
        locations.longitude, 
        locations.updated_at 
    FROM Icon
    INNER JOIN locations ON Icon.user_id = locations.user_id
    WHERE Icon.user_id != 7
');
$friends = $friendStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>友達リストとピン表示</title>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            display: flex;
            height: 100vh;
        }
        #sidebar {
            width: 300px;
            background-color: #f4f4f4;
            overflow-y: auto;
            padding: 10px;
        }
        #map {
            flex: 1;
        }
        .friend-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            cursor: pointer;
        }
        .friend-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div id="sidebar">
    <h2>友達リスト</h2>
    <ul id="friend-list">
        <?php foreach ($friends as $friend): ?>
            <li class="friend-item" data-lat="<?= $friend['latitude'] ?>" data-lng="<?= $friend['longitude'] ?>">
                <img src="<?= $friend['icon_name'] ?>" alt="アイコン">
                <span><?= htmlspecialchars($friend['user_id']) ?> (更新: <?= $friend['updated_at'] ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div id="map"></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

// マップの初期化
const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895],
    zoom: 10
});

// 友達のデータをPHPから受け取る
const friends = <?= json_encode($friends); ?>;
const selfIcon = <?= json_encode($selfIcon['icon_name']); ?>;

// 自分の現在地を表示
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(position => {
        const userLocation = [position.coords.longitude, position.coords.latitude];

        new mapboxgl.Marker({ element: createMarker(selfIcon) })
            .setLngLat(userLocation)
            .setPopup(new mapboxgl.Popup().setHTML('<div>あなたの現在地</div>'))
            .addTo(map);

        map.flyTo({ center: userLocation, zoom: 14 });
    });
}

// カスタムマーカーを作成する関数
function createMarker(iconUrl) {
    const marker = document.createElement('div');
    marker.style.backgroundImage = `url(${iconUrl})`;
    marker.style.width = '40px';
    marker.style.height = '40px';
    marker.style.backgroundSize = 'cover';
    marker.style.borderRadius = '50%';
    return marker;
}

// 友達のピンをマップに表示
friends.forEach(friend => {
    new mapboxgl.Marker({ element: createMarker(friend.icon_name) })
        .setLngLat([friend.longitude, friend.latitude])
        .setPopup(new mapboxgl.Popup().setHTML(`<div>ユーザーID: ${friend.user_id}</div>`))
        .addTo(map);
});

// 友達リストをクリックしたときの動作
document.querySelectorAll('.friend-item').forEach(item => {
    item.addEventListener('click', () => {
        const lat = parseFloat(item.getAttribute('data-lat'));
        const lng = parseFloat(item.getAttribute('data-lng'));

        map.flyTo({ center: [lng, lat], zoom: 14 });
    });
});
</script>

</body>
</html>
