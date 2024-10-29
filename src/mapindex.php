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
      background-image: url('https://babyblue-aso2201203.webdav-lolipop.jp/Nomodon/src/image/4.png'); /* アイコン画像のURLを変更 */
      background-size: contain;
      width: 50px; /* アイコンの幅 */
      height: 50px; /* アイコンの高さ */
      border: none;
      border-radius: 50%;
    }
  </style>
</head>
<body>
  <div id='map'></div>
  <script>
    mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';
    
    // 地図の初期化（仮に日本の中心に設定）
    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/streets-v11',
      center: [139.6917, 35.6895], // 初期表示の中心
      zoom: 10
    });

    // 現在地を取得してピンを立てる関数
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(position => {
        const userLocation = [position.coords.longitude, position.coords.latitude];
        
        // 地図の中心を現在地に移動
        map.setCenter(userLocation);

        // アイコン画像を使用したマーカーを作成
        const markerElement = document.createElement('div');
        markerElement.className = 'marker';

        // 現在地にマーカーを追加
        new mapboxgl.Marker(markerElement)  // アイコンを使用したマーカー
          .setLngLat(userLocation)
          .setPopup(new mapboxgl.Popup({ offset: 25 })
            .setHTML('<div>あなたの現在地です</div>'))
          .addTo(map);
      }, error => {
        console.error('現在地を取得できませんでした:', error);
      });
    } else {
      alert("Geolocationがサポートされていません");
    }
  </script>
</body>
</html>
