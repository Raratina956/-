<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>複数のピンを立てる</title>
  <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
  <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
  <style>
    #map { width: 100%; height: 500px; }
  </style>
</head>
<body>
  <div id='map'></div>
  <script>
    mapboxgl.accessToken = 'YOUR_MAPBOX_ACCESS_TOKEN';

    // 地図の初期化（仮に日本の中心に設定）
    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/streets-v11',
      center: [139.6917, 35.6895], // 初期表示の中心
      zoom: 5
    });

    // 複数の位置情報（都市の経度・緯度と名前）
    const locations = [
      { coordinates: [139.6917, 35.6895], name: '東京' },
      { coordinates: [135.5023, 34.6937], name: '大阪' },
      { coordinates: [139.6380, 35.4437], name: '横浜' },
      { coordinates: [130.4017, 33.5902], name: '福岡' },
      { coordinates: [135.7681, 35.0116], name: '京都' }
    ];

    // 各都市にマーカーを追加
    locations.forEach(location => {
      new mapboxgl.Marker({ color: 'blue' })  // 青色のマーカー
        .setLngLat(location.coordinates)
        .setPopup(new mapboxgl.Popup({ offset: 25 })
          .setHTML(`<div>${location.name}</div>`))  // 都市名をポップアップに表示
        .addTo(map);
    });
  </script>
</body>
</html>
