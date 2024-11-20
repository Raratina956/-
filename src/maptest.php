<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapboxテスト</title>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css" rel="stylesheet" />
    <style>
        #map {
            width: 100%;
            height: 90vh; /* マップの高さを調整 */
        }
        #update-location {
            position: absolute; /* マップ上に配置 */
            top: 10px;
            left: 10px;
            z-index: 999; /* マップより前面に表示 */
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
<button id="update-location">位置情報を更新</button>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

// マップを初期化
const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895],
    zoom: 10
});

// ボタンが動作するか確認
document.getElementById('update-location').addEventListener('click', () => {
    alert('更新ボタンがクリックされました');
});
</script>

</body>
</html>
