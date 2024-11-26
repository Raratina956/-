<?php
require 'parts/auto-login.php';
require 'header.php';

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

?>
<!-- <link rel="stylesheet" href="css/join_tag.css"> -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mob_css/favorite-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/fetch_favorites.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" type="text/css" href="css/fetch_favorites.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

</head>
<h1 class="okini">お気に入り</h1>
<table border="0" style="font-size: 15pt;">
    <tr>
        <th id="allTab" class="selected" onclick="fetchData('all'); selectTab(this)">全て</th>
        <th></th>
        <th id="teacherTab" class="unselected" onclick="fetchData('teacher'); selectTab(this)">先生</th>
        <th></th>
        <th id="studentTab" class="unselected" onclick="fetchData('student'); selectTab(this)">生徒</th>
    </tr>
</table>

<div class="favorite_list" id="favorite-list"></div>

<!-- メイン(マップ)に戻る -->
<button type="button" class="back-link" onclick="location.href='map.php'">戻る</button>

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
            header.classList.remove('active', 'selected');  // ここで 'active' と 'selected' クラスを削除
        });

        // クリックされた項目にのみ 'active' と 'selected' クラスを追加
        let selectedHeader;
        if (type === 'all') {
            selectedHeader = document.querySelector('th[onclick*="fetchData(\'all\')"]');
        } else if (type === 'teacher') {
            selectedHeader = document.querySelector('th[onclick*="fetchData(\'teacher\')"]');
        } else if (type === 'student') {
            selectedHeader = document.querySelector('th[onclick*="fetchData(\'student\')"]');
        }

        if (selectedHeader) {
            selectedHeader.classList.add('active', 'selected');
        } else {
            console.error('選択された要素が見つかりません');
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



