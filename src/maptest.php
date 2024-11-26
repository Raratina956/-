<?php
session_start();
require 'db-connect.php';
try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

$partner_id = $_SESSION['user']['user_id'];

// 自分が送った友達申請を取得
$stmt = $pdo->prepare('
    SELECT Icon.user_id, Icon.icon_name, Users.user_name, locations.latitude, locations.longitude
    FROM FriendRequests
    INNER JOIN Icon ON FriendRequests.friend_id = Icon.user_id
    INNER JOIN Users ON Icon.user_id = Users.user_id
    INNER JOIN locations ON Icon.user_id = locations.user_id
    WHERE FriendRequests.user_id = ?
');
$stmt->execute([$partner_id]);
$friendRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>友達申請リスト</title>
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
    <link rel="stylesheet" href="css/mapindex.css">
</head>
<body>

<div id="sidebar">
    <h2>友達申請リスト</h2>
    <button id="add-friend-btn">友達追加</button>
    <div id="search-container" style="display: none;">
        <input type="text" id="friend-search" placeholder="名前で検索">
    </div>

    <div id="add-friend-form" style="display: none;">
        <input type="text" id="friend-name" placeholder="友達の名前を入力">
        <button id="submit-friend-request">申請</button>
        <button id="cancel-friend-request">キャンセル</button>
    </div>

    <ul id="friend-list">
        <!-- 友達リストはここに追加される -->
    </ul>
    <button id="update-location-btn">位置情報を更新</button>
</div>

<div id='map'></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [130.4017, 33.5902],  // 初期位置を福岡市博多区に設定
    zoom: 12  // ズームレベルを調整
});

// PHPで取得した友達申請中のユーザー情報を表示
const friendRequests = <?php echo json_encode($friendRequests); ?>;

// 友達申請リストを表示する関数
function displayFriendRequests(users) {
    const friendList = document.getElementById('friend-list');
    friendList.innerHTML = ''; // 一度リセットしてから追加
    users.forEach(user => {
        const listItem = document.createElement('li');
        listItem.className = 'friend-item';

        // アイコンと名前を表示
        const userIcon = document.createElement('img');
        userIcon.src = user.icon_name; // アイコン画像
        const userName = document.createElement('span');
        userName.textContent = user.user_name; // ユーザー名

        listItem.appendChild(userIcon);
        listItem.appendChild(userName);

        // 友達リスト項目にクリックイベントを追加
        listItem.addEventListener('click', () => {
            const userPosition = [user.longitude, user.latitude];
            map.flyTo({ center: userPosition, zoom: 15 });

            // クリック時にポップアップ表示
            new mapboxgl.Popup()
                .setLngLat(userPosition)
                .setHTML(`<div>ユーザー名: ${user.user_name}</div>`)
                .addTo(map);
        });

        friendList.appendChild(listItem);
    });
}

// 初期表示
displayFriendRequests(friendRequests);

// 友達追加ボタンのクリックイベント
document.getElementById('add-friend-btn').addEventListener('click', () => {
    document.getElementById('search-container').style.display = 'none'; // 検索バーを非表示
    document.getElementById('add-friend-form').style.display = 'block'; // フォームを表示
    document.getElementById('friend-name').focus(); // 入力フィールドにフォーカスを当てる
});

// キャンセルボタンのクリックイベント
document.getElementById('cancel-friend-request').addEventListener('click', () => {
    document.getElementById('add-friend-form').style.display = 'none'; // フォームを非表示
});

// 友達申請の送信イベント
document.getElementById('submit-friend-request').addEventListener('click', () => {
    const friendName = document.getElementById('friend-name').value.trim();

    if (friendName) {
        // サーバーに友達申請を送信（例: save-friend-request.php などにPOST）
        fetch('save-friend-request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: "<?php echo $partner_id; ?>",
                friend_name: friendName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('友達申請が送信されました');
                document.getElementById('add-friend-form').style.display = 'none'; // フォームを非表示
            } else {
                alert('友達申請に失敗しました');
            }
        })
        .catch(error => {
            console.error('友達申請の送信に失敗しました:', error);
            alert('友達申請の送信に失敗しました');
        });
    } else {
        alert('名前を入力してください');
    }
});

// 現在地を取得し、自分のマーカーを表示
function updateLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLocation = [position.coords.longitude, position.coords.latitude];

            map.setCenter(userLocation);

            const myMarkerElement = document.createElement('div');
            myMarkerElement.className = 'marker';
            myMarkerElement.style.backgroundImage = `url(<?php echo json_encode($iconUrl); ?>)`;

            new mapboxgl.Marker(myMarkerElement)
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
        }, {
            enableHighAccuracy: true,  // 高精度を要求
            timeout: 10000,            // タイムアウト時間を指定（例：10秒）
            maximumAge: 0              // 以前の位置情報を再利用しない
        });
    } else {
        alert("Geolocationがサポートされていません");
    }
}

// 位置情報更新ボタンのクリックイベント
document.getElementById('update-location-btn').addEventListener('click', updateLocation);

</script>

</body>
</html>
