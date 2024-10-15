<?php
require 'parts/auto-login.php';
?>

<?php
// require 'header.php';
?>
<h1>お知らせ</h1>

<?php
$list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
$list_sql->execute([$_SESSION['user']['user_id']]);
$list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
if ($list_raw) {
    echo '<table>';
    foreach ($list_raw as $row) {
        $announcement_id = $row['announcement_id'];
        $info_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
        $info_sql->execute([$announcement_id]);
        $info_row = $info_sql->fetch();
        echo '<tr>';
        echo '<td rowspan="2">アイコン</td>';
        $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
        $user_sql->execute([$row['send_person']]);
        $user_row = $user_sql->fetch();
        echo '<td>', $user_row['user_name'], 'さんが、アナウンスをしました</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>', $info_row['content'], '</td>';
        echo '</tr>';
    }
    echo '<table>';
}else{
    echo 'お知らせがありません';
}
?>

</table>