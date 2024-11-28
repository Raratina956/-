<?php
require 'parts/auto-login.php';
require 'header.php';

function handleTag($pdo, $tag_id, $user_id) {
    $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
    $sql->execute([$tag_id, $user_id]);
    return $sql->fetch(PDO::FETCH_ASSOC);
}

function insertTag($pdo, $tag_id, $user_id) {
    $sql_insert = $pdo->prepare('INSERT INTO Tag_attribute (tag_id,user_id) VALUES (?,?)');
    $sql_insert->execute([$tag_id, $user_id]);
}

function deleteTag($pdo, $tag_id, $user_id) {
    $sql_delete = $pdo->prepare('DELETE FROM Tag_attribute WHERE tag_id=? AND user_id=?');
    $sql_delete->execute([$tag_id, $user_id]);
}

if (isset($_POST['tag_id'])) {
    $regi_tag_id = $_POST['tag_id'];
    $row = handleTag($pdo, $regi_tag_id, $_SESSION['user']['user_id']);
    if (!$row) {
        insertTag($pdo, $regi_tag_id, $_SESSION['user']['user_id']);
    } else {
        deleteTag($pdo, $regi_tag_id, $_SESSION['user']['user_id']);
    }
}
?>

<!-- HTML部分 -->
<link rel="stylesheet" href="mob_css/tag_list-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/tag_list.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
<link rel="stylesheet" href="css/tag_list.css" media="screen and (min-width: 1280px)">

<!-- フォントリンク -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

<div class="center">
<h1 class="title okini" id="okini" style="font-size: 2.5em; color: #333;">ｍｙタグ一覧</h1>
    <a href="join_tag.php" class="join_tag"><span>参加しているタグはこちら</span></a>
    <form action="tag_list" method="post">
        <input type="text" name="tag_search" class="textbox" placeholder="検索したい内容を入力してください">
        <input type="submit" value="検索" class="search">
    </form>
</div>


<?php
function limitDisplay($text, $limit) {
    return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . '...' : $text;
}

function getTagResults($pdo, $tag_search = '') {
    if (empty($tag_search)) {
        $query = "SELECT * FROM Tag_list";
        $stmt = $pdo->query($query);
    } else {
        $search_sql = $pdo->prepare("SELECT * FROM Tag_list WHERE tag_name=?");
        $search_sql->execute([$tag_search]);
        $stmt = $search_sql;
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$results = isset($_POST['tag_search']) ? getTagResults($pdo, $_POST['tag_search']) : getTagResults($pdo);
$judge = isset($_POST['tag_search']) ? 0 : 1;

if ($results) {
    echo '<br><a href="map.php" class="back-link">戻る</a><br>';
    echo '<div class="tag_list_table"><table id="table" border="0"><th>タグ名</th><th>参加人数</th><th>作成者</th><th></th>';

    foreach ($results as $row) {
        $sql_count = $pdo->prepare('SELECT COUNT(DISTINCT user_id) AS user_count FROM Tag_attribute WHERE tag_id = ?');
        $sql_count->execute([$row['tag_id']]);
        $user_count = $sql_count->fetch(PDO::FETCH_ASSOC)['user_count'];

        if ($user_count != 0 || $judge == 0) {
            echo '<tr>';
            echo '<td>', limitDisplay($row['tag_name'], 10), '</td>';
            echo '<td>', $user_count, '</td>';
            $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
            $sql_user->execute([$row['user_id']]);
            echo '<td>', limitDisplay($sql_user->fetch(PDO::FETCH_ASSOC)['user_name'], 10), '</td>';
            echo '<form action="tag_list.php" method="post"><input type="hidden" name="tag_id" value="', $row['tag_id'], '">';
            if (!empty($_POST['tag_search'])) echo '<input type="hidden" name="tag_search" value="', $_POST['tag_search'], '">';
            $is_joined = handleTag($pdo, $row['tag_id'], $_SESSION['user']['user_id']);
            echo '<td><input type="submit" value="', $is_joined ? '参加済' : '参加', '" class="', $is_joined ? 'joined' : 'join', '"></td>';
            echo '</form></tr>';
        }
    }

    echo '</table></div>';
} else {
    echo '<br><center>タグがありません</center><br><br><br><a href="map.php" class="back-link">マップへ</a>';
}
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const okiniElement = document.querySelector('.okini');
    const text = okiniElement.textContent;
    okiniElement.innerHTML = '';

    for (let i = 0; i < text.length; i++) {
        const span = document.createElement('span');
        span.textContent = text[i];
        span.style.animationDelay = `${i * 0.5}s`; // 0.5秒ごとに遅延を設定
        okiniElement.appendChild(span);
    }
});

</script>
