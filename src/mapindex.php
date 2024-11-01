<?php
require 'db-connect.php';
try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

// 現在のユーザーの情報を取得
$partner_id = $_GET['user_id'];
$iconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id = ?');
$iconStmt->execute([$partner_id]);
$icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
$iconUrl = $icon['icon_name'];

// 他のユーザーの位置情報を取得
$userStmt = $pdo->prepare('
    SELECT locations.user_id, locations.latitude, locations.longitude, Icon.icon_name 
    FROM locations 
    INNER JOIN Icon ON locations.user_id = Icon.user_id 
    WHERE locations.user_id != ?
');
$userStmt->execute([$partner_id]);
$otherUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>現在地にピンを立てる</title>
  <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
  <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
  <style>
    #map { width: 100%; height: 500px; }
    .marker {
      background-size: contain;
      width: 50px;
      height: 50px;
      border: none;
      border-radius: 50%;
    }
  </style>
</head>
<body>
  
  <div id='map'></div>
  <script>
  mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

  const iconUrl = "<?php echo $iconUrl; ?>";
  const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895],
    zoom: 10
  });

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(position => {
      const userLocation = [position.coords.longitude, position.coords.latitude];

      map.setCenter(userLocation);

      // 現在のユーザーのマーカー
      const markerElement = document.createElement('div');
      markerElement.className = 'marker';
      markerElement.style.backgroundImage = `url(${iconUrl})`;

      new mapboxgl.Marker(markerElement)
        .setLngLat(userLocation)
        .setPopup(new mapboxgl.Popup({ offset: 25 })
          .setHTML('<div>あなたの現在地です</div>'))
        .addTo(map);

      // 他のユーザーのマーカー
      const otherUsers = <?php echo json_encode($otherUsers); ?>;
      otherUsers.forEach(user => {
        const otherMarkerElement = document.createElement('div');
        otherMarkerElement.className = 'marker';
        otherMarkerElement.style.backgroundImage = `url(${user.icon_name})`;
        
        new mapboxgl.Marker(otherMarkerElement)
          .setLngLat([user.longitude, user.latitude])
          .setPopup(new mapboxgl.Popup({ offset: 25 })
            .setHTML(`<div>User ID: ${user.user_id}</div>`))
          .addTo(map);
      });
    });
  } else {
    alert("Geolocationがサポートされていません");
  }
  </script>

</body>
</html>
