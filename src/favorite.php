<?php
require 'parts/auto-login.php';

if (isset($_POST['delete'])) {
    $delete = $_POST['delete'];
    $sql_delete = $pdo->prepare('DELETE FROM Favorite WHERE favorite_id = ?');
    $result = $sql_delete->execute([$delete]);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '削除に失敗しました。']);
    }
    exit;
}

require 'header.php';
?>
<link rel="stylesheet" href="css/join_tag.css">
<link rel="stylesheet" href="css/fetch_favorites.css">
<h1>お気に入り</h1>
<table border="0" style="font-size: 15pt;">
    <tr>
        <th onclick="fetchData('all')">全て</th>
        <th></th>
        <th onclick="fetchData('teacher')">先生</th>
        <th></th>
        <th onclick="fetchData('student')">生徒</th>
    </tr>
</table>

<div id="favorite-list">
    <!-- ここに取得したデータが表示される -->
    <!-- fetch_favoritesにあるよ！ -->
</div>
<a href="main.php" class="back-link">メインへ</a>
<script>
function fetchData(type) {
    console.log('fetchDataが呼ばれました。'); // fetchDataの呼び出しログ
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_favorites.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('favorite-list').innerHTML = xhr.responseText;
        }
    };

    xhr.send('type=' + type);
}

function deleteFavorite(favoriteId) {
    console.log('削除するID:', favoriteId); // 削除するIDのログ
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'favorite.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                fetchData('all'); // 削除後に全てのデータを再取得
            } else {
                console.error(response.message);
                alert(response.message);
            }
        } else {
            console.error(`リクエストエラー: ステータスコード ${xhr.status}`);
            alert(`リクエストに失敗しました。ステータスコード: ${xhr.status}`);
        }
    };

    xhr.onerror = function() {
        console.error('ネットワークエラーが発生しました。');
        alert('ネットワークエラーが発生しました。');
    };

    xhr.send('delete=' + favoriteId);
}

function fetchData(type) {
    console.log('fetchDataが呼ばれました。'); // fetchDataの呼び出しログ
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_favorites.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('favorite-list').innerHTML = xhr.responseText;
        }
    };

    xhr.send('type=' + type);

    // クリックされた要素に'active'クラスを付与し、それ以外の要素からは削除
    const headers = document.querySelectorAll('th');
    headers.forEach(function(header) {
        header.classList.remove('active');
    });

    // クリックされた項目にのみ 'active' クラスを追加
    if (type === 'all') {
        document.querySelector('th[onclick="fetchData(\'all\')"]').classList.add('active');
    } else if (type === 'teacher') {
        document.querySelector('th[onclick="fetchData(\'teacher\')"]').classList.add('active');
    } else if (type === 'student') {
        document.querySelector('th[onclick="fetchData(\'student\')"]').classList.add('active');
    }
}

// ページが読み込まれたときに全てのデータを表示
fetchData('all');
</script>

