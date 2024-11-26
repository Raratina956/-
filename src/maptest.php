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
    center: [139.6917, 35.6895],  // 初期位置は東京に設定
    zoom: 10
});

// 他のユーザーの位置情報を取得
const otherUsers = <?php echo json_encode($allLocations); ?>;

// 友達一覧を作成
const friendList = document.getElementById('friend-list');
otherUsers.forEach(user => {
    const listItem = document.createElement('li');
    listItem.className = 'friend-item';

    // アイコンと名前を表示
    const userIcon = document.createElement('img');
    userIcon.src = user.icon_name; // アイコン画像
    const userName = document.createElement('span');
    userName.textContent = user.user_name; // ユーザー名

    listItem.appendChild(userIcon);
    listItem.appendChild(userName);

    // 友達リスト項目にクリックイベントを追加
    listItem.addEventListener('click', () => {
        const userPosition = [user.longitude, user.latitude];
        map.flyTo({ center: userPosition, zoom: 15 });

        // クリック時にポップアップ表示
        new mapboxgl.Popup()
            .setLngLat(userPosition)
            .setHTML(`<div>ユーザー名: ${user.user_name}</div>`)
            .addTo(map);
    });

    friendList.appendChild(listItem);
});

// 現在地を取得し、自分のマーカーを表示
function updateLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLocation = [position.coords.longitude, position.coords.latitude];

            map.setCenter(userLocation);

            const myMarkerElement = document.createElement('div');
            myMarkerElement.className = 'marker';
            myMarkerElement.style.backgroundImage = `url(${<?php echo json_encode($iconUrl); ?>})`;

            new mapboxgl.Marker(myMarkerElement)
                .setLngLat(userLocation)
                .setPopup(new mapboxgl.Popup({ offset: 25 })
                    .setHTML('<div>あなたの現在地です</div>'))
                .addTo(map);

            // 現在地をサーバーに送信
            fetch('save-location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: "<?php echo $partner_id; ?>",
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('位置情報が保存されました:', data);
            })
            .catch(error => {
                console.error('位置情報の保存に失敗しました:', error);
            });
        }, error => {
            console.error('現在地を取得できませんでした:', error);
        }, {
            enableHighAccuracy: true,  // 高精度を要求
            timeout: 10000,            // タイムアウト時間を指定（例：10秒）
            maximumAge: 0              // 以前の位置情報を再利用しない
        });
    } else {
        alert("Geolocationがサポートされていません");
    }
}

// 位置情報更新ボタンのクリックイベント
document.getElementById('update-location-btn').addEventListener('click', updateLocation);

// 他のユーザーのマーカーを表示
otherUsers.forEach(user => {
    const markerElement = document.createElement('div');
    markerElement.className = 'marker';
    markerElement.style.backgroundImage = `url(${user.icon_name})`;

    const userPosition = [user.longitude, user.latitude];

    new mapboxgl.Marker(markerElement)
        .setLngLat(userPosition)
        .setPopup(new mapboxgl.Popup({ offset: 25 })
            .setHTML(`<div>ユーザー名: ${user.user_name}</div>`))
        .addTo(map);
});
</script>

</body>
</html>
