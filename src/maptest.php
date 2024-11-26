<?php
session_start();
require 'db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $selfUserId = 7; // 自分のID

    // 他のユーザーの情報を取得（アイコンURLを含む）
    $friendStmt = $pdo->prepare("
    SELECT locations.user_id, latitude, longitude, updated_at, Icon.icon_name 
    FROM locations 
    LEFT JOIN Icon ON locations.user_id = Icon.user_id
    WHERE locations.user_id != ?
");

    $friendStmt->execute([$selfUserId]);
    $friends = $friendStmt->fetchAll(PDO::FETCH_ASSOC);

    // 自分のアイコンと位置情報を取得
    $selfStmt = $pdo->prepare("SELECT icon_name, latitude, longitude FROM locations WHERE user_id = ?");
    $selfStmt->execute([$selfUserId]);
    $selfLocation = $selfStmt->fetch(PDO::FETCH_ASSOC);
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
        .friend-item span {
            margin-left: 10px;
        }
        .icon-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .icon-modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
        }
        button {
            margin-top: 10px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div id="sidebar">
    <h2>友達リスト</h2>
    <ul id="friend-list">
        <?php foreach ($friends as $friend): ?>
            <li class="friend-item" data-lat="<?= $friend['latitude'] ?>" data-lng="<?= $friend['longitude'] ?>">
                <img src="<?= htmlspecialchars($friend['icon_name']) ?>" alt="アイコン" width="30" height="30">
                <span>ユーザーID: <?= htmlspecialchars($friend['user_id']) ?></span>
                <span>(更新: <?= $friend['updated_at'] ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
    <button id="update-location-btn">位置情報を更新</button>
</div>

<!-- モーダル -->
<div class="icon-modal" id="icon-modal">
    <img id="modal-icon" src="" alt="拡大アイコン">
</div>

<div id="map"></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895], // 初期位置（東京）
    zoom: 10
});

const selfUserId = 7; // 自分のID
const selfIcon = 'img/self-icon.png'; // 自分のアイコンURL

// PHPから友達情報をJSON形式でJavaScriptに渡す
const friends = <?php echo json_encode($friends); ?>;
const selfLocation = <?php echo json_encode($selfLocation); ?>;

// 自分の位置情報をマップに表示
if (selfLocation.latitude && selfLocation.longitude) {
    const selfMarker = new mapboxgl.Marker({ element: createCustomMarker(selfLocation.icon_name || selfIcon) })
        .setLngLat([selfLocation.longitude, selfLocation.latitude])
        .setPopup(new mapboxgl.Popup().setHTML('<div>あなたの現在地</div>'))
        .addTo(map);
}

// 友達の位置情報をマップに表示
friends.forEach(friend => {
    if (friend.latitude && friend.longitude) {
        const marker = new mapboxgl.Marker({ element: createCustomMarker(friend.icon_name) })
            .setLngLat([friend.longitude, friend.latitude])
            .setPopup(new mapboxgl.Popup().setHTML(`<div>ユーザーID: ${friend.user_id}</div>`))
            .addTo(map);
    }
});

// カスタムマーカー作成関数
function createCustomMarker(iconUrl) {
    const img = document.createElement('img');
    img.src = iconUrl;
    img.alt = 'アイコン';
    img.style.width = '30px';
    img.style.height = '30px';
    img.style.borderRadius = '50%'; // 丸型にする
    img.style.cursor = 'pointer';
    img.addEventListener('click', () => {
        const modal = document.getElementById('icon-modal');
        const modalIcon = document.getElementById('modal-icon');
        modalIcon.src = iconUrl;
        modal.style.display = 'flex';
    });
    return img;
}

// 友達リストをクリックしたとき
document.querySelectorAll('.friend-item img').forEach(item => {
    item.addEventListener('click', () => {
        const modal = document.getElementById('icon-modal');
        const modalIcon = document.getElementById('modal-icon');
        modalIcon.src = item.src; // クリックされたアイコンをモーダルに表示
        modal.style.display = 'flex';
    });
});

// モーダルを閉じる
document.getElementById('icon-modal').addEventListener('click', () => {
    document.getElementById('icon-modal').style.display = 'none';
});

// 位置情報を更新
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
</script>

</body>
</html>
