<?php
require 'parts/auto-login.php';
?>

<?php
// require 'header.php';
?>
<h1>参加タグ一覧</h1>
<?php
$search_sql = $pdo->prepare("SELECT * FROM Tag_attribute WHERE user_id=?");
$search_sql->execute([$_SESSION['user']['user_id']]);
$results = $search_sql->fetchAll(PDO::FETCH_ASSOC);
if ($results) {
    ?>
    <table>
        <th>タグ名</th>
        <th>作成者</th>
        <th></th>
        <?php
        foreach ($results as $row) {
            $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
            $sql_tag->execute([$row['tag_id']]);
            $row_tag = $sql_tag->fetch();
            // echo '<tr>';
            // echo '<td>',$row_tag['tag_name'],'</td>';
            // $sql_user = $pdo->prepare('SELECT * FROM User WHERE user_id=?');
            // $sql_user->execute([$row_tag['user_id']]);
            // $row_user = $sql_user->fetch();
            // echo '<td>',$row_user['user_name'],'</td>';
            // echo '<td></td>';
            // echo '</tr>';
        }
        ?>
    </table>
    <?php
}else{
    echo '参加済みのタグがありません';
}
?>