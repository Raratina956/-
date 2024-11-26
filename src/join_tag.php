<?php
require 'parts/auto-login.php';
if (isset($_POST['delete_tag'])) {
    $delete_tag = $_POST['delete_tag'];
    $sql_delete = $pdo->prepare('DELETE FROM Tag_attribute WHERE tag_id=? AND user_id=?');
    $sql_delete->execute([$delete_tag, $_SESSION['user']['user_id']]);
}
?>

<?php
require 'header.php';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="mob_css/j-tag.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/join_tag.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
<link rel="stylesheet" href="css/join_tag.css" media="screen and (min-width: 1280px)">
<!-- font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

<main>
    <h1>参加タグ一覧</h1>
    <?php
    $search_sql = $pdo->prepare("SELECT * FROM Tag_attribute WHERE user_id=?");
    $search_sql->execute([$_SESSION['user']['user_id']]);
    $results = $search_sql->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        ?>
        <div class="tag_list_table">
            <table id="table" border="0" style="font-size: 18pt;">
                <th>タグ名</th>
                <th>作成者</th>
                <th></th>
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
                foreach ($results as $row) {
                    $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
                    $sql_tag->execute([$row['tag_id']]);
                    $row_tag = $sql_tag->fetch();
                    echo '<tr>';
                    echo '<td>', limitDisplay($row_tag['tag_name'], 10), '</td>';
                    $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                    $sql_user->execute([$row_tag['user_id']]);
                    $row_user = $sql_user->fetch();
                    echo '<td>', limitDisplay($row_user['user_name'], 10), '</td>';
                    echo '<td>';
                    ?>
                    <form action="join_tag.php" method="post">
                        <input type="hidden" name="delete_tag" value=<?php echo $row['tag_id']; ?>>
                        <input type="submit" value="退会" class="button_quit">
                    </form>
                    <?php
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </table>
        </div>
        <?php
    } else {
        echo '<h3>参加済みのタグがありません</h3>';
    }

    ?>
    <a href="tag_list.php" class="back-link">戻る</a>
</main>