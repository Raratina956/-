<?php
require 'db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // `locations`テーブルからすべてのレコードを取得
    $stmt = $pdo->prepare("SELECT * FROM locations");
    $stmt->execute();
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>位置情報マップ</title>
  <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
  <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
  <style>
    #map { width: 100%; height: 500px; }
    .marker {
      background-size: cover;
      width: 50px;
      height: 50px;
      border-radius: 50%;
    }
  </style>
</head>
<body>
  <h1>位置情報マップ</h1>
  <div id="map"></div>

  <script>
    mapboxgl.accessToken = 'your_actual_mapbox_access_token';

    // PHPから位置情報データをJavaScriptに渡す
    const locations = <?php echo json_encode($locations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    // 地図の初期化
    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/streets-v11',
      center: [139.6917, 35.6895], // 中心座標（東京）
      zoom: 10
    });

    // 位置情報データを使ってマーカーを追加
    locations.forEach(location => {
      const markerElement = document.createElement('div');
      markerElement.className = 'marker';
      markerElement.style.backgroundImage = 'url("https://example.com/path/to/icon.png")'; // マーカーのアイコン画像

      // 地図にマーカーを追加
      new mapboxgl.Marker(markerElement)
        .setLngLat([location.longitude, location.latitude])
        .setPopup(new mapboxgl.Popup({ offset: 25 })
          .setHTML(`<div>User ID: ${location.user_id}<br>Updated: ${location.updated_at}</div>`))
        .addTo(map);
    });
  </script>
</body>
</html>
