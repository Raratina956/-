<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <title>Map Example</title>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVOfQ2kHq-JVAYMwjZXA8V2UNLPXePMls"></script>
    <script>
        let map; // グローバル変数として地図を保持
        let marker; // マーカーをグローバル変数として保持

        function initMap() {
            // 地図の初期化
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 8,
                center: { lat: -34.397, lng: 150.644 }, // 初期中心位置
            });

            // 初期マーカーの位置
            const position = { lat: -34.397, lng: 150.644 };
            marker = new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: position,
                content: '<div style="color: #000;">Hello World!</div>', // マーカーのコンテンツ
            });

            // 「自分の位置に移動」ボタンのイベントリスナーを追加
            document.getElementById('locateButton').addEventListener('click', locateUser);
        }

        function locateUser() {
            // 位置情報の取得
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(success, error);
            } else {
                alert("このブラウザは位置情報をサポートしていません。");
            }
        }

        function success(pos) {
            const position = {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
            };

            // 地図の中心を現在の位置に移動
            map.setCenter(position);

            // マーカーの位置を更新
            marker.setMap(null); // 以前のマーカーを削除
            marker = new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: position,
                content: '<div style="color: #000;">ここがあなたの位置です</div>',
            });
        }

        function error() {
            alert("位置情報の取得に失敗しました。");
        }
    </script>
</head>

<body>
    <div id="map" style="height: 500px; width: 100%;"></div>
    <button id="locateButton">自分の位置に移動</button>
    <script>
        // 地図を初期化
        window.onload = initMap;
    </script>
</body>

</html>
