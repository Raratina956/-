<?php
session_start();
require 'db-connect.php';
require 'parts/auto-login.php';
require 'header.php';
unset($_SESSION['floor']['kai']);

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 自分のID (例: セッションから取得する場合)
    $selfUserId = $_SESSION['user']['user_id'] ?? 7;

    // 他のユーザーの情報を取得
    $friendStmt = $pdo->prepare("
        SELECT user_id, latitude, longitude, updated_at 
        FROM locations 
        WHERE user_id != ?
    ");
    $friendStmt->execute([$selfUserId]);
    $friends = $friendStmt->fetchAll(PDO::FETCH_ASSOC);

    // プルダウン用のタグデータ取得
    $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE user_id=?');
    $sql->execute([$selfUserId]);
    $results = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>統合マップ</title>

    <!-- Mapbox -->
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css" rel="stylesheet" />

    <!-- CSS -->
    <link rel="stylesheet" href="mob_css/map-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/map.css" media="screen and (min-width: 1280px)">
    <style>
        body {
            margin: 0;
            display: flex;
            height: 100vh;
        }
        #sidebar {
            width: 300px;
            background-color: #f4f4f4;
            overflow-y: auto;
            padding: 10px;
        }
        #map {
            flex: 1;
        }
        .friend-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            cursor: pointer;
        }
        .friend-item span {
            margin-left: 10px;
        }
        .icon-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }
        .icon-modal video {
            width: 700px;
        }
        @media screen and (max-width: 480px) {
            .icon-modal video {
                width: 300px;
            }
        }
    </style>
</head>
<body>
<?php
if (!isset($_COOKIE['img_displayed'])) {
    echo '<div class="icon-modal" id="icon-modal">
            <video id="icon" autoplay loop muted>
                <source src="img/icon_move.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
          </div>';
    setcookie('img_displayed', 'true', time() + (86400 * 30), "/");
}
?>

<div id="sidebar">
    <h2>友達リスト</h2>
    <ul id="friend-list">
        <?php foreach ($friends as $friend): ?>
            <li class="friend-item" data-lat="<?= $friend['latitude'] ?>" data-lng="<?= $friend['longitude'] ?>">
                <span>ユーザーID: <?= htmlspecialchars($friend['user_id']) ?></span>
                <span>(更新: <?= $friend['updated_at'] ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
    <button id="update-location-btn">位置情報を更新</button>

    <form action="map.php" method="post">
        <label>お気に入り:
            <input type="checkbox" name="favorite" value="yes" <?php echo ($_POST['favorite'] ?? 'no') === 'yes' ? 'checked' : ''; ?>>
        </label>
        <label>タグ:
            <select name="tag_list">
                <?php
                $selected_tag = $_POST['tag_list'] ?? '0';
                echo '<option value="0"', ($selected_tag === '0' ? ' selected' : ''), '>全て</option>';
                foreach ($results as $result) {
                    echo '<option value="' . $result['tag_id'] . '"' . ($result['tag_id'] == $selected_tag ? ' selected' : '') . '>' . $result['tag_name'] . '</option>';
                }
                ?>
            </select>
        </label>
        <button type="submit">絞込</button>
    </form>
</div>

<div id="map"></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [139.6917, 35.6895],
    zoom: 10
});

// 自分の位置情報を更新
document.getElementById('update-location-btn').addEventListener('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLocation = [position.coords.longitude, position.coords.latitude];
            map.flyTo({ center: userLocation, zoom: 14 });
            new mapboxgl.Marker()
                .setLngLat(userLocation)
                .setPopup(new mapboxgl.Popup().setHTML('<div>あなたの現在地</div>'))
                .addTo(map);
        });
    } else {
        alert('位置情報が取得できません。');
    }
});

// 友達の位置情報をマップに表示
const friends = <?= json_encode($friends); ?>;
friends.forEach(friend => {
    new mapboxgl.Marker()
        .setLngLat([friend.longitude, friend.latitude])
        .setPopup(new mapboxgl.Popup().setHTML(`<div>ユーザーID: ${friend.user_id}</div>`))
        .addTo(map);
});

// モーダル表示処理
window.onload = function() {
    const iconModal = document.getElementById('icon-modal');
    const icon = document.getElementById('icon');
    if (iconModal) {
        iconModal.style.display = 'flex';
        setTimeout(() => icon.style.opacity = '0', 1500);
        setTimeout(() => iconModal.style.display = 'none', 2000);
    }
};
</script>
</body>
</html>
