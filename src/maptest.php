<?php
session_start();
require 'db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 自分のID (例: セッションから取得する場合)
    $selfUserId = 7;

    // ユーザーの位置情報とアイコン情報を取得
    $friendStmt = $pdo->prepare("
        SELECT l.user_id, l.latitude, l.longitude, l.updated_at, i.icon_name
        FROM locations l
        LEFT JOIN Icon i ON l.user_id = i.user_id
        WHERE l.user_id != ?
    ");
    $friendStmt->execute([$selfUserId]);
    $friends = $friendStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>友達リストとカスタムピン表示</title>
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
        .friend-item span {
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div id="sidebar">
    <h2>友達リスト</h2>
    <ul id="friend-list">
        <?php foreach ($friends as $friend): ?>
            <li class="friend-item" data-lat="<?= $friend['latitude'] ?>" data-lng="<?= $friend['longitude'] ?>">
                <span>ユーザーID: <?= htmlspecialchars($friend['user_id']) ?></span>
                <span>(更新: <?= $friend['updated_at'] ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
    <button id="update-location-btn">位置情報を更新kai</button>
</div>

<div id="map"></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895],
    zoom: 10
});

const selfUserId = 7; // 自分のID
const selfIcon = 'img/self-icon.png'; // 自分のアイコンURL

// 自分の位置情報を取得・更新
document.getElementById('update-location-btn').addEventListener('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLocation = [position.coords.longitude, position.coords.latitude];

            map.flyTo({ center: userLocation, zoom: 14 });

            new mapboxgl.Marker()
                .setLngLat(userLocation)
                .setPopup(new mapboxgl.Popup().setHTML('<div>あなたの現在地</div>'))
                .addTo(map);

            // データベースに送信
            fetch('update_location.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: selfUserId,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            })
            .then(response => response.json())
            .then(data => console.log(data))
            .catch(error => console.error(error));
        });
    } else {
        alert('位置情報が取得できません。');
    }
});

// 友達の位置情報をマップに表示
const friends = <?= json_encode($friends); ?>;
friends.forEach(friend => {
    const markerElement = document.createElement('div');
    markerElement.style.backgroundImage = `url('img/${friend.icon_name || 'default-icon.png'}')`;
    markerElement.style.width = '30px';
    markerElement.style.height = '30px';
    markerElement.style.backgroundSize = 'cover';
    markerElement.style.borderRadius = '50%';

    new mapboxgl.Marker(markerElement)
        .setLngLat([friend.longitude, friend.latitude])
        .setPopup(new mapboxgl.Popup().setHTML(`<div>ユーザーID: ${friend.user_id}</div>`))
        .addTo(map);
});

// 友達リストをクリックしたとき
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
