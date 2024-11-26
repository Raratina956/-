<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>友達追加</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css" rel="stylesheet" />
    <style>
        /* ポップアップスタイル */
        #popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            display: none;
            z-index: 1000;
        }
        #popup .popup-content {
            text-align: center;
        }
        #popup input {
            margin: 10px 0;
        }

        /* 地図スタイル */
        #map {
            width: 100%;
            height: 500px;
        }

        #sidebar {
            width: 250px;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #f4f4f4;
            padding: 10px;
        }

        #friend-list {
            list-style: none;
            padding: 0;
        }

        .friend-item {
            display: flex;
            align-items: center;
            padding: 5px;
            cursor: pointer;
        }

        .friend-item img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }

    </style>
</head>
<body>

<!-- サイドバー（友達リストと更新ボタン） -->
<div id="sidebar">
    <h2>友達一覧</h2>
    <ul id="friend-list">
        <!-- 友達リストはここに追加される -->
    </ul>
    <button id="update-location-btn">位置情報を更新</button>
</div>

<!-- 地図 -->
<div id="map"></div>

<!-- 友達追加ボタン -->
<button id="add-friend-btn">友達追加</button>

<!-- ポップアップ -->
<div id="popup">
    <div class="popup-content">
        <h3>友達追加</h3>
        <p>名前で検索してください。</p>
        <input type="text" id="friend-search-popup" placeholder="名前で検索">
        <button id="send-request-btn">申請を送る</button>
    </div>
    <button id="close-popup-btn">閉じる</button>
</div>

<script>
// Mapbox設定
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [130.4017, 33.5902],  // 初期位置を福岡市博多区に設定
    zoom: 12  // ズームレベルを調整
});

// 友達一覧を作成
const friendList = document.getElementById('friend-list');
const otherUsers = <?php echo json_encode($allLocations); ?>;  // PHPで取得したユーザー情報

otherUsers.forEach(user => {
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

    // 他のユーザーの位置情報をマーカーで表示
    const markerElement = document.createElement('div');
    markerElement.className = 'marker';
    markerElement.style.backgroundImage = `url(${user.icon_name})`;

    new mapboxgl.Marker(markerElement)
        .setLngLat([user.longitude, user.latitude])
        .setPopup(new mapboxgl.Popup({ offset: 25 })
            .setHTML(`<div>ユーザー名: ${user.user_name}</div>`))
        .addTo(map);
});

// 友達追加ボタンを押したときにポップアップを表示
$('#add-friend-btn').on('click', function() {
    $('#popup').show();
});

// ポップアップ閉じるボタン
$('#close-popup-btn').on('click', function() {
    $('#popup').hide();
});

// ユーザー検索（例: Ajaxで検索）
$('#friend-search-popup').on('input', function() {
    const query = $(this).val();
    // ユーザー検索処理をここに追加（例: Ajaxで検索）
});

// 申請送信
$('#send-request-btn').on('click', function() {
    const receiverUserId = $('#friend-search-popup').val(); // 検索したユーザーのIDを取得
    if (receiverUserId) {
        $.ajax({
            url: 'send-request.php',
            type: 'POST',
            data: { receiver_user_id: receiverUserId },
            success: function(response) {
                alert(response.message);
                $('#popup').hide();  // 申請後、ポップアップを閉じる
            },
            error: function() {
                alert('エラーが発生しました');
            }
        });
    } else {
        alert('ユーザーを選択してください');
    }
});

// 位置情報更新ボタンのクリックイベント
$('#update-location-btn').on('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLocation = [position.coords.longitude, position.coords.latitude];

            map.setCenter(userLocation);

            const myMarkerElement = document.createElement('div');
            myMarkerElement.className = 'marker';
            myMarkerElement.style.backgroundImage = `url(<?php echo json_encode($iconUrl); ?>)`; // ユーザーのアイコンを表示

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
        });
    } else {
        alert("Geolocationがサポートされていません");
    }
});
</script>

</body>
</html>
