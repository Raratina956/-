<?php
require 'parts/auto-login.php';

$announcements = [];
$ann_send_list_row = [];
$ann_sent_list_row = [];

// 送信リストを取得
$ann_send_list_sql = $pdo->prepare('SELECT * FROM Notification WHERE send_person=?');
$ann_send_list_sql->execute([$_SESSION['user']['user_id']]);
$ann_send_list_row = $ann_send_list_sql->fetchAll(PDO::FETCH_ASSOC);

// 受信リストを取得
$ann_check_list_sql = $pdo->prepare('SELECT * FROM Announce_his WHERE sent_person=?');
$ann_check_list_sql->execute([$_SESSION['user']['user_id']]);
$ann_check_list_row = $ann_check_list_sql->fetchAll(PDO::FETCH_ASSOC);

if ($ann_check_list_row) {
    foreach ($ann_check_list_row as $check_row) {
        $ann_sent_list_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
        $ann_sent_list_sql->execute([$check_row['announcement_id']]);
        $ann_sent_list = $ann_sent_list_sql->fetchAll(PDO::FETCH_ASSOC);

        // 受信リストをマージして保持
        $ann_sent_list_row = array_merge($ann_sent_list_row, $ann_sent_list);
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
}
?>

<?php require 'header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mob_css/announce_his-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/announce_his.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" type="text/css" href="css/announce_his.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

</head>
<div class="container">
    <table>
        <thead>
            <tr>
                <th>
                    <select id="filterType" class="filter" onchange="filterAnnouncements()">
                        <option value="all">全て</option>
                        <option value="send">送信</option>
                        <option value="receive">受信</option>
                    </select>
                </th>
                <th>タイトル</th>
                <th>投稿者</th>
                <th>投稿先</th>
                <th>投稿日時</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($announcements as $announcement) {
                $typeClass = ($announcement['ann_type'] === 1) ? 'send' : 'receive';
                echo '<tr class="announcement-row ' . $typeClass . '">';
                switch ($announcement['ann_type']) {
                    case 1:
                        echo '<td data-label="種別">送信</td>';
                        break;
                    case 2:
                        echo '<td data-label="種別">受信</td>';
                        break;
                    default:
                        echo '<td data-label="種別">エラー</td>';
                        break;
                }
                echo '<td data-label="タイトル">' . $announcement['title'] . '</td>';
                echo '<td data-label="投稿者">' . $announcement['send_user_name'] . '</td>';
                echo '<td data-label="投稿先">' . $announcement['sent_tag_name'] . '</td>';
                echo '<td data-label="投稿日時">' . $announcement['send_time'] . '</td>';
                echo '<form action="announce_his_info.php" method ="post">';
                echo '<input type="hidden" name="announcement_id" value=' . $announcement['announcement_id'] . '>';
                echo '<td><input type="submit" value="詳細" class="detail"></td>';
                echo '</form>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    <!-- 絞り込み結果がない場合のエラーメッセージ表示用 -->
    <div id="noAnnouncementMessage" style="color:red; margin-top:10px; display:none;">該当するアナウンスはありません</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function filterAnnouncements() {
            const filter = document.getElementById("filterType").value;
            const rows = document.querySelectorAll(".announcement-row");
            let visibleRowCount = 0;

            rows.forEach(row => {
                if (filter === "all") {
                    row.style.display = "";
                    visibleRowCount++;
                } else if (filter === "send" && row.classList.contains("send")) {
                    row.style.display = "";
                    visibleRowCount++;
                } else if (filter === "receive" && row.classList.contains("receive")) {
                    row.style.display = "";
                    visibleRowCount++;
                } else {
                    row.style.display = "none";
                }
            });

            // エラーメッセージの表示・非表示を切り替え
            const errorMessage = document.getElementById("noAnnouncementMessage");
            if (visibleRowCount === 0) {
                errorMessage.style.display = "block";
            } else {
                errorMessage.style.display = "none";
            }
        }

        document.getElementById("filterType").addEventListener("change", filterAnnouncements);
    });
</script>

<div class="back-button">
    <form action="announce.php" method="GET">
        <button type="submit">戻る</button>
    </form>
</div>
