<?php
session_start();
require 'db-connect.php';
require 'parts/auto-login.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

$partner_id = $_SESSION['user']['user_id'];

// ユーザーのアイコンを取得
$iconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id = ?');
$iconStmt->execute([$partner_id]);
$icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
$iconUrl = $icon ? $icon['icon_name'] : 'default-icon.png'; // デフォルトアイコンを設定

$followStmt = $pdo->prepare('
    SELECT 
        Favorite.follower_id, 
        Icon.icon_name, 
        Users.user_name, 
        locations.latitude, 
        locations.longitude 
    FROM Favorite
    LEFT JOIN Icon ON Favorite.follower_id = Icon.user_id
    LEFT JOIN Users ON Favorite.follower_id = Users.user_id
    LEFT JOIN locations ON Favorite.follower_id = locations.user_id
    WHERE Favorite.follow_id = ?
    AND locations.latitude IS NOT NULL
    AND locations.longitude IS NOT NULL
');
$followStmt->execute([$partner_id]);
$followedUsers = $followStmt->fetchAll(PDO::FETCH_ASSOC);

// モバイル版かどうかを判定
function isMobile() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $mobileAgents = ['iPhone', 'Android', 'iPad', 'iPod', 'Windows Phone', 'BlackBerry'];
    foreach ($mobileAgents as $agent) {
        if (strpos($userAgent, $agent) !== false) {
            return true;
        }
    }
    return false;
}
$isMobile = isMobile();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>現在地にピンを立てる</title>
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
    <link rel="stylesheet" href="css/mapindex.css">
</head>
<body>
<div id="sidebar">
    <button id="back-btn">前のページに戻る</button>

    <?php if ($isMobile): ?>
        <!-- スマホ版: ハンバーガーメニュー -->
        <button id="menu-btn">☰ メニュー</button>
        <nav id="menu" class="hidden">
            <h2>友達一覧</h2>
            <ul id="friend-list">
                <!-- JavaScriptで友達リストを生成 -->
            </ul>
        </nav>
    <?php else: ?>
        <!-- PC版: サイドバー -->
        <h2>友達一覧</h2>
        <ul id="friend-list">
            <!-- JavaScriptで友達リストを生成 -->
        </ul>
    <?php endif; ?>

    <button id="update-location-btn">位置情報を更新</button>
</div>
<div id='map'></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [130.4017, 33.5902], // 初期位置：福岡市の中心
    zoom: 12
});

// PC/モバイル共通：友達リストのデータ
const followedUsers = <?php echo json_encode($followedUsers); ?>;
console.log('フォローしているユーザーのデータ:', followedUsers);

// PC版の友達リスト作成
const friendList = document.getElementById('friend-list');
followedUsers.forEach(user => {
    if (user.icon_name && user.user_name) {
        const listItem = document.createElement('li');
        listItem.className = 'friend-item';

        // アイコンと名前を表示
        const userIcon = document.createElement('img');
        userIcon.src = user.icon_name;
        userIcon.alt = `${user.user_name}のアイコン`;
        userIcon.style.width = '32px';

        const userName = document.createElement('span');
        userName.textContent = user.user_name;

        listItem.appendChild(userIcon);
        listItem.appendChild(userName);

        // クリックでマップ移動
        listItem.addEventListener('click', () => {
            const userPosition = [user.longitude, user.latitude];
            map.flyTo({ center: userPosition, zoom: 15 });

            new mapboxgl.Popup()
                .setLngLat(userPosition)
                .setHTML(`<div>ユーザー名: ${user.user_name}</div>`)
                .addTo(map);
        });

        friendList.appendChild(listItem);
    }
});

// スマホ版メニュー
<?php if ($isMobile): ?>
    const menuBtn = document.getElementById('menu-btn');
    const menu = document.getElementById('menu');

    menuBtn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
    });
<?php endif; ?>

// 現在地の更新
document.getElementById('update-location-btn').addEventListener('click', () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLocation = [position.coords.longitude, position.coords.latitude];
            map.setCenter(userLocation);

            new mapboxgl.Marker()
                .setLngLat(userLocation)
                .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML('<div>あなたの現在地です</div>'))
                .addTo(map);
        });
    } else {
        alert("Geolocationがサポートされていません");
    }
});
</script>
</body>
</html>
