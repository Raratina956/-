<?php
require 'parts/auto-login.php';
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

<?php
// require 'header.php';
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
            echo '<td>', $row_user['user_name'], '</td>';
            echo '<form action="tag_list.php" method="post">';
            echo '<input type="hidden" name="tag_id" value=', $row['tag_id'], '>';
            $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
            $sql->execute([$row['tag_id'], $_SESSION['user']['user_id']]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                echo '<td><input type="submit" value="参加"></td>';
            }else{
                echo '<td><input type="submit" value="参加済"></td>';
            }
            echo '</form>';
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