<?php
require 'db-connect.php';
try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}
$partner_id = intval($_GET['user_id']); // ユーザーIDを整数に変換
$iconStmt = $pdo->prepare('SELECT icon_name, user_name FROM Icon INNER JOIN Users ON Icon.user_id = Users.user_id WHERE Icon.user_id = ?');
$iconStmt->execute([$partner_id]);
$user = $iconStmt->fetch(PDO::FETCH_ASSOC);
$iconUrl = $user['icon_name'];
$userName = $user['user_name'];
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
      width: 50px;  /* アイコンの幅 */
      height: 50px; /* アイコンの高さ */
      border: none;
      border-radius: 50%;
    }
  </style>
</head>
<body>

  <div>
    <h1><?php echo htmlspecialchars($userName); ?>の位置情報</h1>
    <img src="<?php echo htmlspecialchars($iconUrl); ?>" alt="<?php echo htmlspecialchars($userName); ?>のアイコン" style="width:50px;height:50px;">
  </div>

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

      // マーカー要素の作成
      const markerElement = document.createElement('div');
      markerElement.className = 'marker';
      markerElement.style.backgroundImage = `url(${iconUrl})`;
      markerElement.style.backgroundSize = 'contain'; // アイコンが正しく表示されるように調整
      markerElement.style.width = '50px'; // マーカーの幅
      markerElement.style.height = '50px'; // マーカーの高さ

      // マーカーを地図に追加
      new mapboxgl.Marker(markerElement)
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
</script>

</body>
</html>
