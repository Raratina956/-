<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapbox 現在地更新</title>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css" rel="stylesheet" />
    <style>
        #map {
            width: 100%;
            height: 90vh; /* マップの高さを調整 */
        }
        #update-location {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 999;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #update-location:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div id="map"></div>
<button id="update-location">現在地を更新</button>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895], // 初期位置: 東京
    zoom: 10
});

// 現在地用のマーカーを定義（初期状態では未作成）
let userMarker = null;

// 現在地を取得し、マップを更新する関数
function updateUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLocation = [position.coords.longitude, position.coords.latitude];

            if (userMarker) {
                // すでにマーカーがある場合は位置を更新
                userMarker.setLngLat(userLocation);
            } else {
                // 初回のボタンクリックでマーカーを作成
                userMarker = new mapboxgl.Marker({ color: 'red' })
                    .setLngLat(userLocation)
                    .setPopup(new mapboxgl.Popup({ offset: 25 })
                        .setHTML('<div>現在地</div>'))
                    .addTo(map);
            }

            // マップの中心を現在地に移動
            map.flyTo({ center: userLocation, zoom: 14 });
        }, error => {
            console.error('位置情報を取得できませんでした:', error);
            alert('現在地を取得できませんでした。');
        });
    } else {
        alert('このブラウザはGeolocationをサポートしていません。');
    }
}

// ボタンをクリックしたときに現在地を更新
document.getElementById('update-location').addEventListener('click', updateUserLocation);
</script>

</body>
</html>
