<?php
require 'parts/auto-login.php';
require 'header.php';
if (isset($_POST['tag_id'])) {
    $regi_tag_id = $_POST['tag_id'];
    $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
    $sql->execute([$regi_tag_id, $_SESSION['user']['user_id']]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $sql_insert = $pdo->prepare('INSERT INTO Tag_attribute (tag_id,user_id) VALUES (?,?)');
        $sql_insert->execute([
            $regi_tag_id,
            $_SESSION['user']['user_id']
        ]);
    } else {
        $sql_delete = $pdo->prepare('DELETE FROM Tag_attribute WHERE tag_id=? AND user_id=?');
        $sql_delete->execute([$regi_tag_id, $_SESSION['user']['user_id']]);
    }
}
?>

<link rel="stylesheet" href="mob_css/tag_list-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/tag_list.css" media="screen and (min-width: 1280px)">
<!-- font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&display=swap" rel="stylesheet">
<!-- 
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet"> -->

<div class="center">
<h1>みんなのタグ</h1>
<a href="join_tag.php" class="join_tag"><span>参加しているタグはこちら</span></a>
<form action="tag_list" method="post">
    <input type="text" name="tag_search" class="textbox" placeholder="検索したい内容を入力してください">
    <input type="submit" value="検索" class="search">
</form>
</div>
<?php
function limitDisplay($text, $limit) {
    // Check if the text exceeds the limit
    if (mb_strlen($text) > $limit) {
        // Return the limited text with ellipsis
        return mb_substr($text, 0, $limit) . '...';
    } else {
        // Return the original text if within the limit
        return $text;
    }
}

if (isset($_POST['tag_search'])) {
    if (empty($_POST['tag_search'])) {
        $query = "SELECT * FROM Tag_list";
        $stmt = $pdo->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $judge = 0;
    } else {
        $tag_search = $_POST['tag_search'];
        $search_sql = $pdo->prepare("SELECT * FROM Tag_list WHERE tag_name=?");
        $search_sql->execute([$tag_search]);
        $results = $search_sql->fetchAll(PDO::FETCH_ASSOC);
        $judge = 0;
        echo '<center>【',$tag_search,'】の検索結果</center>';
    }

} else {
    $query = "SELECT * FROM Tag_list";
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $judge = 1;
}
if ($results) {
    ?>
    <br>
    <a href="map.php" class="back-link">戻る</a>
    <br>
    <table id="table" border="0">
        <th>タグ名</th>
        <th>参加人数</th>
        <th>作成者</th>
        <th></th>
        <?php
foreach ($results as $row) {
    $sql_count = $pdo->prepare('SELECT COUNT(DISTINCT user_id) AS user_count FROM Tag_attribute WHERE tag_id = ?');
    $sql_count->execute([$row['tag_id']]);
    $count_result = $sql_count->fetch(PDO::FETCH_ASSOC);
    $user_count = $count_result['user_count'];
    if ($user_count != 0 || $judge == 0) {
        echo '<tr>';
        echo '<td>', limitDisplay($row['tag_name'], 10), '</td>';
        echo '<td>', $user_count, '</td>';
        $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
        $sql_user->execute([$row['user_id']]);
        $row_user = $sql_user->fetch();
        echo '<td>', limitDisplay($row_user['user_name'], 10), '</td>';
        echo '<form action="tag_list.php" method="post">';
        echo '<input type="hidden" name="tag_id" value=', $row['tag_id'], '>';
        if (!(empty($_POST['tag_search']))) {
            echo '<input type="hidden" name="tag_search" value="', $tag_search, '">';
        }
        $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
        $sql->execute([$row['tag_id'], $_SESSION['user']['user_id']]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            echo '<td><input type="submit" value="参加" class="join"></td>';
        } else {
            echo '<td><input type="submit" value="参加済" class="joined"></td>';
        }
        echo '</form>';
        echo '</tr>';
    }
}
?>

    </table>
    <?php
} else {
    echo '<br><center>タグがありません</center>';
    echo '<br><br><br>';
    echo '<a href="map.php" class="back-link">マップへ</a>';
}
?>

