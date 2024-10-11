<?php
require 'parts/auto-login.php';
?>

<?php
// require 'header.php';
?>

<h1>お気に入り</h1>
<table>
    <th>全て</th>
    <th>先生</th>
    <th>生徒</th>
</table>
<?php
$all_sql = $pdo->prepare('SELECT * FROM Favorite WHERE follow_id=?');
$all_sql->execute([$_SESSION['user']['user_id']]);
$list_raw = $all_sql->fetchAll(PDO::FETCH_ASSOC);
if ($list_raw) {
    echo '<table>';
    foreach ($list_raw as $favorite) {
        echo '<tr>';
        echo '<td>アイコン（仮）</td>';
        $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
        $sql_user->execute([$favorite['follower_id']]);
        $row_user = $sql_user->fetch();
        if($row_user['s_or_t']===0){
            echo '<td>', $row_user['user_name'], '</td>';
        }else{
            echo '<td>', $row_user['user_name'], '　先生</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo 'お気に入りのユーザーがいません';
}
?>