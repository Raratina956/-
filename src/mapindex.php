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

// ユーザーのアイコンを取得
$iconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id = ?');
$iconStmt->execute([$partner_id]);
$icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
$iconUrl = $icon ? $icon['icon_name'] : 'default-icon.png'; // デフォルトアイコンを設定

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
<div id='map'></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895], // 初期位置：東京
    zoom: 10
});

// 他のユーザーの位置情報を取得
const otherUsers = <?php echo json_encode($allLocations); ?>;
console.log('他のユーザーのデータ:', otherUsers);

// 現在地を取得し、自分のマーカーを表示
function updateLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLocation = [position.coords.longitude, position.coords.latitude];
            map.setCenter(userLocation);

            const myMarkerElement = document.createElement('div');
            myMarkerElement.className = 'marker';
            myMarkerElement.style.backgroundImage = `url(${<?php echo json_encode($iconUrl); ?>})`;
            myMarkerElement.style.width = '40px';
            myMarkerElement.style.height = '40px';

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
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
    } else {
        alert("Geolocationがサポートされていません");
    }
}

// 位置情報更新ボタンのクリックイベントを削除
// document.getElementById('update-location-btn').addEventListener('click', updateLocation);

// 他のユーザーのマーカーを表示
otherUsers.forEach(user => {
    if (user.icon_name && user.latitude && user.longitude) {
        const markerElement = document.createElement('div');
        markerElement.className = 'marker';
        markerElement.style.backgroundImage = `url(${user.icon_name})`;
        markerElement.style.width = '32px';
        markerElement.style.height = '32px';

        const userPosition = [user.longitude, user.latitude];

        new mapboxgl.Marker(markerElement)
            .setLngLat(userPosition)
            .setPopup(new mapboxgl.Popup({ offset: 25 })
                .setHTML(`<div>ユーザー名: ${user.user_name}</div>`))
            .addTo(map);
    }
});
</script>

</body>
</html>
