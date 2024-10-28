<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8" />
    <title>hoge</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script defer>
        // ボタンクリック時
        $(document).ready(function() {
            $('#send').click(function() {
                // 位置情報の取得可否により分岐
                navigator.geolocation.getCurrentPosition(success, fail);
            });
        });

        // 位置情報が取得できた場合
        function success(pos) {
            var date = new Date(pos.timestamp);
            const position = {
                data: date.toLocaleString(),                // 日時
                lat: pos.coords.latitude,                   // 緯度
                lon: pos.coords.longitude,             // 経度
                alt: pos.coords.altitude,              // 高度
                posacc: pos.coords.accuracy,           // 位置精度
                altacc: pos.coords.altitudeAccuracy,   // 高度精度
                head: pos.coords.heading,              // 移動方向
                speed: pos.coords.speed                // 速度
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
                // 通信が成功した場合　
                success: function(data, dataType) {
                    alert(data); // サーバーサイドからの返答を表示させてみる
                },
                // 通信が失敗した場合
                error: function() {
                    alert('失敗らしい');
                }
            });
        }

        // 位置情報が取得できなかった場合
        function fail(error) {
            if (error.code == 1) alert('位置情報を取得する時に許可がない')
            if (error.code == 2) alert('何らかのエラーが発生し位置情報が取得できなかった。')
            if (error.code == 3) alert('タイムアウト　制限時間内に位置情報が取得できなかった。')
        }
    </script>
</head>

<body>
    <button type="button" id="send">ボタン</button>
</body>

</html>