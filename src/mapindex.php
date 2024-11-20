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
    <!-- 更新ボタンを追加 -->
    <button id="update-location">位置情報を更新</button>
</div>
<div id='map'></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895],
    zoom: 10
});

const updateButton = document.getElementById('update-location');

// 他のユーザーの位置情報を取得
const otherUsers = <?php echo json_encode($allLocations); ?>;

// 地図に他のユーザーのマーカーを表示する関数
function displayOtherUsers() {
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
}

// 初期表示で他のユーザーのマーカーを追加
displayOtherUsers();

// 現在地を取得してマーカーを更新する関数
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
        });
    } else {
        alert("Geolocationがサポートされていません");
    }
}

// 更新ボタンにイベントを設定
updateButton.addEventListener('click', updateLocation);

</script>

</body>
</html>
