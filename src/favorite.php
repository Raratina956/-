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
        <span id="teacher" class="clickable" onclick="filterFavorites('teacher')">先生</span>
        <th></th>
        <span id="student" class="clickable active" onclick="filterFavorites('student')">生徒</span>
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


// クリックとフィルタリングを処理する関数
function filterFavorites(type) {
    // クリックできる全ての要素を取得
    const studentElement = document.getElementById('student');
    const teacherElement = document.getElementById('teacher');
    
    // すべての要素から「active」クラスを削除し、クリックされた要素に追加
    studentElement.classList.remove('active');
    teacherElement.classList.remove('active');

    // クリックされた要素に「active」クラスを追加
    if (type === 'student') {
        studentElement.classList.add('active');
        fetchFavorites('student');
    } else if (type === 'teacher') {
        teacherElement.classList.add('active');
        fetchFavorites('teacher');
    } else {
        // 「全て」フィルタ用のロジックを追加する場合はこちらに記述
        fetchFavorites('all');
    }
}

// タイプに基づいてお気に入りを取得するモック関数
function fetchFavorites(type) {
    // タイプに基づいたフェッチ処理
    console.log('Fetching favorites for:', type);
}
</script>

