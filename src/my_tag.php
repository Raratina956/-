<?php
require 'parts/auto-login.php';
if (isset($error)) {
    unset($error);
}
if (isset($_POST['tag_name'])) {
    if (!(empty($_POST['tag_name']))) {
        $tag_name = $_POST['tag_name'];
        $sql_insert = $pdo->prepare('INSERT INTO Tag_list (tag_name,user_id) VALUES (?,?)');
        $sql_insert->execute([
            $tag_name,
            $_SESSION['user']['user_id']
        ]);
        $lastInsertId = $pdo->lastInsertId();
        $sql_insert = $pdo->prepare('INSERT INTO Tag_attribute (tag_id,user_id) VALUES (?,?)');
        $sql_insert->execute([
            $lastInsertId,
            $_SESSION['user']['user_id']
        ]);
    } else {
        $error = '文字を入力してください';
    }
}
if (isset($_POST['join_tag_id'])) {
    $regi_tag_id = $_POST['join_tag_id'];
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
<?php
require 'header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mob_css/my_tag-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/my_tag.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" type="text/css" href="css/my_tag.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

</head>
<div class="center">
    <h1>ｍｙタグ一覧</h1>
    <h2>タグ作成</h2>
    <form action="my_tag.php" method="post">
        <span>タグ名：</span>
        <input type="name" name="tag_name" class="textbox" maxlength="15">
        <input type="submit" value="作成" class="button_in">
    </form>
</div>
<?php
function limitDisplay($text, $limit)
{
    // Check if the text exceeds the limit
    if (mb_strlen($text) > $limit) {
        // Return the limited text with ellipsis
        return mb_substr($text, 0, $limit) . '...';
    } else {
        // Return the original text if within the limit
        return $text;
    }
}

if (isset($error)) {
    echo '<span style="display: block; text-align: center; color: red;">' . $error . '</span>';
}
$list_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE user_id=?');
$list_sql->execute([$_SESSION['user']['user_id']]);
$list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
if ($list_raw) {
    ?>
    <br><br>
    <div class="tag_list_table">
        <table id="table" style="font-size: 18pt;">
            <th>タグ名</th>
            <th></th>
            <th></th>
            <th></th>
            <?php
            foreach ($list_raw as $row) {
                echo '<tr>';
                echo '<td>', limitDisplay($row['tag_name'], 10), '</td>';
                ?>
                <form action="my_tag.php" method="post">
                    <input type="hidden" name="join_tag_id" value=<?php echo $row['tag_id']; ?>>
                    <?php
                    $sql_tag = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
                    $sql_tag->execute([$row['tag_id'], $_SESSION['user']['user_id']]);
                    $row_tag = $sql_tag->fetch(PDO::FETCH_ASSOC);
                    if (!$row_tag) {
                        echo '<td><input type="submit" value="参加" class="join"></td>';
                    } else {
                        echo '<td><input type="submit" value="参加済" class="joined"></td>';
                    }
                    ?>
                </form>
                <form action="tag_update.php" method="post">
                    <input type="hidden" name="tag_id" value=<?php echo $row['tag_id']; ?>>
                    <td><input type="submit" value="更新" class="button_up"></td>
                </form>
                <form action="delete_tag.php" method="post">
                    <input type="hidden" name="delete_tag_id" value=<?php echo $row['tag_id']; ?>>
                    <td><input type="submit" value="削除" class="button_del"></td>
                </form>
                <?php
                echo '</tr>';
            }
            ?>
        </table>
    </div>
    <?php
} else {
    echo '<div class="tag_list_table">';
    echo '<div class="text">';
    echo '作成されたタグがありません';
    echo '</div>';
    echo '</div>';
}
?>
<a href="map.php" class="back-link">戻る</a>