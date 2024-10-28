<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <title>位置情報取得と地図表示</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVOfQ2kHq-JVAYMwjZXA8V2UNLPXePMls"></script> <!-- Google Maps APIのスクリプトを非同期で読み込み -->
    <script defer>
        let map; // グローバル変数として地図を定義
        let marker; // グローバル変数としてマーカーを定義

        $(document).ready(function() {
            $('#send').click(function() {
                navigator.geolocation.getCurrentPosition(success, fail);
            });
        });

        function success(pos) {
            var date = new Date(pos.timestamp);
            const position = {
                data: date.toLocaleString(),                // 日時
                lat: pos.coords.latitude,                   // 緯度
                lon: pos.coords.longitude,                  // 経度
                alt: pos.coords.altitude,                   // 高度
                posacc: pos.coords.accuracy,                // 位置精度
                altacc: pos.coords.altitudeAccuracy,        // 高度精度
                head: pos.coords.heading,                    // 移動方向
                speed: pos.coords.speed                      // 速度
            };

            // サーバーサイドへPOSTする
            $.ajax({
                type: "post",
                url: "server_side.php",
                data: {
                    "date": position.data,
                    "lat": position.lat,
                    "lon": position.lon,
                    "alt": position.alt,
                    "posacc": position.posacc,
                    "altacc": position.altacc,
                    "head": position.head,
                    "speed": position.speed
                },
                success: function(data, dataType) {
                    alert(data); // サーバーサイドからの返答を表示
                },
                error: function() {
                    alert('失敗らしい');
                }
            });

            // 位置情報をページに表示
            $('#result').html(`
                <h3>位置情報</h3>
                <p>日時: ${position.data}</p>
                <p>緯度: ${position.lat}</p>
                <p>経度: ${position.lon}</p>
                <p>高度: ${position.alt}</p>
                <p>位置精度: ${position.posacc}</p>
                <p>高度精度: ${position.altacc}</p>
                <p>移動方向: ${position.head}</p>
                <p>速度: ${position.speed}</p>
            `);

            // マップを表示
            initMap(position.lat, position.lon);
        }

        function fail(error) {
            if (error.code == 1) alert('位置情報を取得する時に許可がない');
            if (error.code == 2) alert('何らかのエラーが発生し位置情報が取得できなかった。');
            if (error.code == 3) alert('タイムアウト　制限時間内に位置情報が取得できなかった。');
        }

        function initMap(lat, lon) {
            const position = { lat: parseFloat(lat), lng: parseFloat(lon) }; // 緯度と経度をオブジェクトに

            // 地図を初期化
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: position,
            });

            // AdvancedMarkerElementを使用してマーカーを設置
            marker = new google.maps.marker.AdvancedMarkerElement({
                position: position,
                map: map,
                content: '<div style="color: black;">あなたの位置</div>' // マーカーに表示する内容
            });
        }
    </script>
</head>

<body>
    <button type="button" id="send">位置情報取得</button>
    <div id="result"></div> <!-- 結果を表示するための要素 -->
    <div id="map" style="height: 400px; width: 100%;"></div> <!-- 地図を表示するための要素 -->
</body>

</html>
