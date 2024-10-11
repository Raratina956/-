<?php
require 'parts/auto-login.php';

if (isset($_POST['delete'])) {
    $delete = $_POST['delete'];
    $sql_delete = $pdo->prepare('DELETE FROM Favorite WHERE favorite_id = ?');
    $result = $sql_delete->execute([$delete]);

    // 成功した場合は成功メッセージを返す
    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '削除に失敗しました。']);
    }
    exit; // 処理が完了したら終了
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
</div>

<script>
function fetchData(type) {
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
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'favorite.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                fetchData('all'); // 削除後に全てのデータを再取得
            } else {
                console.error(response.message); // エラーメッセージをコンソールに表示
                alert(response.message); // アラートでエラーメッセージを表示
            }
        } else {
            console.error('リクエストに失敗しました。');
            alert('リクエストに失敗しました。');
        }
    };

    xhr.send('delete=' + favoriteId);
}

// ページが読み込まれたときに全てのデータを表示
fetchData('all');
</script>
