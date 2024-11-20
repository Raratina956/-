<?php
session_start();
require 'db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 自分のユーザーIDを設定（例: 7）
    $user_id = 7;

    // 自分以外のユーザー情報を取得
    $stmt = $pdo->prepare('
        SELECT 
            Icon.user_id, 
            Icon.icon_name, 
            locations.latitude, 
            locations.longitude 
        FROM Icon
        INNER JOIN locations ON Icon.user_id = locations.user_id
        WHERE Icon.user_id != :user_id
    ');
    $stmt->execute(['user_id' => $user_id]);
    $allLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapboxテスト</title>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            display: flex;
            height: 100vh;
            flex-direction: column;
        }
        #sidebar {
            width: 300px;
            background-color: #f4f4f4;
            overflow-y: auto;
            padding: 10px;
            flex-shrink: 0;
        }
        #map {
            flex: 1;
            position: relative;
        }
        .friend-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            cursor: pointer;
        }
        .friend-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        #update-button {
            position: absolute;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        #update-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div id="sidebar">
    <h2>友達一覧</h2>
    <ul id="friend-list">
        <!-- 友達リストはここに追加される -->
        <?php foreach ($allLocations as $location): ?>
            <li class="friend-item">
                <img src="<?php echo $location['icon_name']; ?>" alt="アイコン">
                <span><?php echo $location['user_id']; ?> さん</span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div id="map"></div>
<button id="update-button">更新</button>

<script>
    mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v11',
        center: [139.6917, 35.6895],
        zoom: 10
    });

    // マーカー作成関数
    function createMarker(iconUrl) {
        const markerElement = document.createElement('div');
        markerElement.className = 'marker';
        markerElement.style.width = '40px';
        markerElement.style.height = '40px';
        markerElement.style.backgroundImage = `url(${iconUrl})`;
        markerElement.style.backgroundSize = 'cover';
        return markerElement;
    }

    // 初期ロード
    function loadMarkers() {
        const otherUsers = <?php echo json_encode($allLocations); ?>;

        otherUsers.forEach(friend => {
            new mapboxgl.Marker({ element: createMarker(friend.icon_name) })
                .setLngLat([friend.longitude, friend.latitude])
                .setPopup(new mapboxgl.Popup().setHTML(`<div>${friend.user_id} さん</div>`))
                .addTo(map);
        });
    }

    // 更新ボタンの動作
    document.getElementById('update-button').addEventListener('click', () => {
        fetch('fetch-locations.php')
            .then(response => response.json())
            .then(data => {
                console.log('最新データ:', data);

                // 既存のピンを削除する (必要に応じて実装)
                document.querySelectorAll('.mapboxgl-marker').forEach(marker => marker.remove());

                // 新しいデータでピンを再描画
                data.forEach(friend => {
                    new mapboxgl.Marker({ element: createMarker(friend.icon_name) })
                        .setLngLat([friend.longitude, friend.latitude])
                        .setPopup(new mapboxgl.Popup().setHTML(`<div>${friend.user_id} さん</div>`))
                        .addTo(map);
                });
            })
            .catch(error => console.error('データ取得エラー:', error));
    });

    // 初期マーカー描画
    loadMarkers();
</script>

</body>
</html>
