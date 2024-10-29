<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mapbox Example</title>
  <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
  <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
  <style>
    #map { width: 100%; height: 500px; }
  </style>
</head>
<body>
  <div id='map'></div>
  <script>
    mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';
    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/streets-v11',
      center: [139.6917, 35.6895], // 東京の中心座標
      zoom: 10
    });

    // マーカーの追加
    const marker = new mapboxgl.Marker()
      .setLngLat([139.6917, 35.6895])
      .addTo(map);

    // ポップアップ（情報ウィンドウ）の追加
    const popup = new mapboxgl.Popup({ offset: 25 })
      .setHTML('<div style="color: #000;">Hello Tokyo!</div>');
    
    marker.setPopup(popup);
  </script>
</body>
</html>
