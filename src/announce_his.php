<?php
require 'parts/auto-login.php';

$announcements = [];
$ann_send_list_row = [];
$ann_sent_list_row = [];

$ann_send_list_sql = $pdo->prepare('SELECT * FROM Notification WHERE send_person=?');
$ann_send_list_sql->execute([$_SESSION['user']['user_id']]);
$ann_send_list_row = $ann_send_list_sql->fetchAll(PDO::FETCH_ASSOC);

$ann_check_list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=? AND type=?');
$ann_check_list_sql->execute([$_SESSION['user']['user_id'], 1]);
$ann_check_list_row = $ann_check_list_sql->fetchAll(PDO::FETCH_ASSOC);
if ($ann_check_list_row) {
    foreach ($ann_check_list_row as $check_row) {
        $ann_sent_list_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
        $ann_sent_list_sql->execute([$check_row['announcement_id']]);
        $ann_sent_list_row = $ann_sent_list_sql->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($ann_send_list_row || $ann_sent_list_row) {
    if ($ann_send_list_row) {
        foreach ($ann_send_list_row as $ann_row) {
            $send_user_id = $ann_row['send_person'];
            $sent_tag_id = $ann_row['sent_tag'];
            $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
            $user_sql->execute([$send_user_id]);
            $user_row = $user_sql->fetch(PDO::FETCH_ASSOC);
            $tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
            $tag_sql->execute([$sent_tag_id]);
            $tag_row = $tag_sql->fetch(PDO::FETCH_ASSOC);
            $announcements[] = [
                'announcement_id' => $ann_row['announcement_id'],
                'title' => $ann_row['title'],
                'content' => $ann_row['content'],
                'send_user_name' => $user_row['user_name'],
                'sent_tag_name' => $tag_row['tag_name'],
                'send_time' => $ann_row['sending_time'],
                'ann_type' => 1,
            ];
        }
    }
    if ($ann_sent_list_row) {
        foreach ($ann_sent_list_row as $ann_row) {
            $send_user_id = $ann_row['send_person'];
            $sent_tag_id = $ann_row['sent_tag'];
            $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
            $user_sql->execute([$send_user_id]);
            $user_row = $user_sql->fetch(PDO::FETCH_ASSOC);
            $tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
            $tag_sql->execute([$sent_tag_id]);
            $tag_row = $tag_sql->fetch(PDO::FETCH_ASSOC);
            $announcements[] = [
                'announcement_id' => $ann_row['announcement_id'],
                'title' => $ann_row['title'],
                'content' => $ann_row['content'],
                'send_user_name' => $user_row['user_name'],
                'sent_tag_name' => $tag_row['tag_name'],
                'send_time' => $ann_row['sending_time'],
                'ann_type' => 2,
            ];
        }
    }
    // 並び替えのための関数を定義
    usort($announcements, function ($a, $b) {
        return strtotime($b['send_time']) <=> strtotime($a['send_time']);
    });
    ?>
    <table>
        <th>種別</th>
        <th>タイトル</th>
        <th>投稿者</th>
        <th>投稿先</th>
        <th>投稿日時</th>
        <th></th>
        <?php
        foreach ($announcements as $announcement) {
            echo '<tr>';
            switch ($announcement['ann_type']) {
                case 1:
                    echo '<td>送信</td>';
                    break;
                case 2:
                    echo '<td>受信</td>';
                    break;
                default:
                    echo '<td>エラー</td>';
                    break;
            }
            echo '<td>'.$announcement['title'].'</td>';
            echo '<td>'.$announcement['send_user_name'].'</td>';
            echo '<td>'.$announcement['sent_tag_name'].'</td>';
            echo '<td>'.$announcement['send_time'].'</td>';
            echo '</tr>';
        }
        ?>
    </table>
    <?php
} else {
    echo '<span>送信したアナウンスがありません</span>';
}
?>