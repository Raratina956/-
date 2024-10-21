<?php
require 'parts/auto-login.php';
?>
<?php
require 'header.php';
?>
<link rel="stylesheet" href="css/info.css">
<h1>お知らせ</h1>

<?php
function timeAgo($datetime)
{
    $now = new DateTime(); // 現在時刻
    $ago = new DateTime($datetime); // 保存された日時
    $diff = $now->diff($ago); // 差分を取得

    // 差分を秒単位で計算
    $diffInSeconds = $now->getTimestamp() - $ago->getTimestamp();

    // 結果の判定
    if ($diffInSeconds < 3600) { // 1時間以内なら
        // 分数で表示
        $minutes = floor($diffInSeconds / 60);
        return $minutes . '分前';
    } elseif ($diffInSeconds < 86400) { // 24時間以内なら
        // 時間数で表示
        $hours = floor($diffInSeconds / 3600);
        return $hours . '時間前';
    } else {
        // 日数で表示
        $days = floor($diffInSeconds / 86400);
        return $days . '日前';
    }
}
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
        echo '<td>アイコン</td>';
        $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
        $user_sql->execute([$info_row['send_person']]);
        $user_row = $user_sql->fetch();
        if($row['type']==1){
            echo '<td>', $user_row['user_name'], 'さんが、アナウンスをしました</td>';
        }elseif ($row['type']==2) {
            echo '<td>', $user_row['user_name'], 'さんが、位置情報を更新しました</td>';
        }
        if($row['read_check']==0){
            echo '<td>未読</td>';
        }
        echo '</tr>';
        echo '<tr>';
        $datetime = $info_row['sending_time'];
        echo '<td>', timeAgo($datetime), '</td>';
        echo '<td class="large-text">', $info_row['content'], '</td>';
        ?>
        <form action="info_detail.php" method="post">
            <input type="hidden" name="announcement_id" value=<?php echo $announcement_id; ?>>
            <td><input type="submit" value="詳細"></td>
        </form>
        <?php
        echo '</tr>';
    }
    echo '<table>';
} else {
    echo 'お知らせがありません';
}
?>

</table>
<a class="back-link" href="main.php">メインへ</a>