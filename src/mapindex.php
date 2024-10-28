<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <title>Map Example</title>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVOfQ2kHq-JVAYMwjZXA8V2UNLPXePMls"></script>
    <script>
        function initMap() {
            // 地図の初期化
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 8,
                center: { lat: -34.397, lng: 150.644 }, // 中心位置
            });

            // マーカーの位置
            const position = { lat: -34.397, lng: 150.644 };

            // AdvancedMarkerElementを使用してマーカーを作成
            const marker = new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: position,
                content: '<div style="color: #000;">Hello World!</div>', // マーカーのコンテンツ
            });
        }
    </script>
</head>

<body>
    <div id="map" style="height: 500px; width: 100%;"></div>
    <script>
        // 地図を初期化
        window.onload = initMap;
    </script>
</body>

</html>
