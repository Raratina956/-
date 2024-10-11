<?php
require 'parts/auto-login.php';
if(isset($_POST['delete'])){
    $delete = $_POST['delete'];
    $sql_delete = $pdo->prepare('DELETE FROM Favorite WHERE favorite_id=?');
    $sql_delete->execute([$delete]);
}
require 'header.php';
?>
<link rel="stylesheet" href="css/join_tag.css">
<h1>お気に入り</h1>
<table>
    <tr>
        <th onclick="fetchData('all')">全て</th>
        <th onclick="fetchData('teacher')">先生</th>
        <th onclick="fetchData('student')">生徒</th>
    </tr>
</table>

<div id="favorite-list">
    <!-- ここに取得したデータが表示される -->
    <!-- fetch_favorites.phpにあるよ -->
</div>

<script>
function fetchData(type) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_favorites.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // サーバーにデータを送信 (type: all, teacher, student)
    xhr.send('type=' + type);

    // 非同期通信が成功した場合
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('favorite-list').innerHTML = xhr.responseText;
        }
    };
}

function deleteFavorite(favoriteId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'favorite.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            fetchData('all'); // 削除後に全てのデータを再取得
        }
    };
    xhr.send('delete=' + favoriteId);
}

// ページが読み込まれたときに全てのデータを表示
fetchData('all');
</script>

