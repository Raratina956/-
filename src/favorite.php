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
        <th id="allTab" class="selected" onclick="fetchData('all'); selectTab(this)">全て</th>
        <th></th>
        <th id="teacherTab" class="unselected" onclick="fetchData('teacher'); selectTab(this)">先生</th>
        <th></th>
        <th id="studentTab" class="unselected" onclick="fetchData('student'); selectTab(this)">生徒</th>
    </tr>
</table>

<div id="favorite-list"></div>

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

    function selectTab(element) {
        // 全ての<th>要素のクラスを"unselected"に
        var ths = document.querySelectorAll('th');
        ths.forEach(th => th.className = 'unselected');

        // 選択された<th>要素のクラスを"selected"に
        element.className = 'selected';
    }

    // ページが読み込まれたときに全てのデータを表示
    window.onload = function() {
        fetchData('all');
        var allTab = document.getElementById('allTab');
        var teacherTab = document.getElementById('teacherTab');
        var studentTab = document.getElementById('studentTab');

        allTab.className = 'selected';
        teacherTab.className = 'unselected';
        studentTab.className = 'unselected';
    };
</script>


