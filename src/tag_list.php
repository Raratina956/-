<?php
require 'parts/auto-login.php';

?>

<?php
require 'header.php';
?>
<h1>タグ一覧</h1>
<?php
$query = "SELECT * FROM Tag_list";
$stmt = $pdo->query($query);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($results) {
    ?>
    <table>
        <th>タグ名</th>
        <th>参加人数</th>
        <th>作成者</th>
        <th></th>
        <?php
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>', $row['tag_name'], '</td>';
            echo '<td></td>';
            $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
            $sql_user->execute([$row['user_id']]);
            $row_user = $sql_user->fetch();
            echo $row['user_id'];
            // echo '<td>',$row_user['user_name'],'</td>';
            echo '<td></td>';
            echo '</tr>';

        }
        ?>
    </table>
    <?php
} else {
    echo 'タグがありません';
}
?>
<a href="main.php">メインへ</a>