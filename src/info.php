<?php
require 'parts/auto-login.php';
function timeAgo($logtime)
{
    if (empty($logtime)) {
        return '日時が設定されていません。'; // 空の日時に対する処理
    }
    $now = new DateTime(); // 現在時刻
    $ago = new DateTime($logtime); // 保存された日時
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
if (isset($_POST['narrow'])) {
    $narrow = $_POST['narrow'];
    $narrow = (int) $narrow;
} else {
    $narrow = 0;
}
if (isset($_POST['n_user'])) {
    $n_user = $_POST['n_user'];
    $n_user = (int) $n_user;
} else {
    $n_user = 0;
}
// 一括既読
if (isset($_POST['all_read'])) {
    // 0:全て 1:アナウンス 2:位置情報
    if ($narrow == 0 && $n_user == 0) {
        $all_read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=?');
        $all_read_sql->execute([1, $_SESSION['user']['user_id']]);
    } else {
        $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
        $list_sql->execute([$_SESSION['user']['user_id']]);
        $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
        if ($list_raw) {
            foreach ($list_raw as $row) {
                switch ($narrow) {
                    case 0:
                        if ($row['type'] == 1) {
                            $n_announce_s = $pdo->prepare('SELECT * FROM Notification WHERE send_person=?');
                            $n_announce_s->execute([$n_user]);
                            $n_announce_r = $n_announce_s->fetch();
                            $announcement_id_a = $n_announce_r['announcement_id'];
                            $announcement_id_a = $row['announcement_id'];
                            $all_read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=? AND announcement_id=?');
                            $all_read_sql->execute([1, $_SESSION['user']['user_id'], 1, $announcement_id_a]);
                        } else if ($row['type']) {
                            $n_announce_s = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
                            $n_announce_s->execute([$n_user]);
                            $n_announce_r = $n_announce_s->fetch();
                            $announcement_id_a = $n_announce_r['current_location_id'];
                            $all_read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=? AND current_location_id=?');
                            $all_read_sql->execute([1, $_SESSION['user']['user_id'], 2, $announcement_id_a]);
                        }
                        break;
                    case 1:
                        if ($n_user == 0) {
                            $all_read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=?');
                            $all_read_sql->execute([1, $_SESSION['user']['user_id'], $narrow]);
                        } else {
                            $n_announce_s = $pdo->prepare('SELECT * FROM Notification WHERE send_person=?');
                            $n_announce_s->execute([$n_user]);
                            $n_announce_r = $n_announce_s->fetch();
                            $announcement_id_a = $n_announce_r['announcement_id'];
                            $all_read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=? AND announcement_id=?');
                            $all_read_sql->execute([1, $_SESSION['user']['user_id'], $narrow, $announcement_id_a]);
                        }
                        break;
                    case 2:
                        if ($n_user == 0) {
                            $all_read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=?');
                            $all_read_sql->execute([1, $_SESSION['user']['user_id'], $narrow]);
                        } else {
                            $n_announce_s = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
                            $n_announce_s->execute([$n_user]);
                            $n_announce_r = $n_announce_s->fetch();
                            $announcement_id_a = $n_announce_r['current_location_id'];
                            $all_read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=? AND current_location_id=?');
                            $all_read_sql->execute([1, $_SESSION['user']['user_id'], $narrow, $announcement_id_a]);
                        }
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }

    }
}
// 一括削除
// if (isset($_POST['all_delete'])) {
//     if ($narrow == 0 && $n_user == 0) {
//         // アナウンス、位置情報 → 全てのアカウント
//         $all_delete_sql = $pdo->prepare("DELETE FROM Announce_check WHERE user_id=?"); 
//         $all_delete_sql->execute([$_SESSION['user']['user_id']]);
//     } else {
//         $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
//         $list_sql->execute([$_SESSION['user']['user_id']]);
//         $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
//         if($list_raw){
//             foreach($list_raw as $row){
//                 switch ($narrow) {
//                     case 1:
//                         $n_announce_s = $pdo->prepare('SELECT * FROM Notification WHERE send_person=?');
//                         $n_announce_s->execute([$n_user]);
//                         $n_announce_r = $n_announce_s->fetch();
//                         $announcement_id_a = $n_announce_r['announcement_id'];
//                         $all_delete_sql = $pdo->prepare("DELETE FROM Announce_check WHERE user_id=? AND type=? AND announcement_id=?");
//                         $all_delete_sql->execute([$_SESSION['user']['user_id'], $narrow, $announcement_id_a]);
//                         break;
//                     case 2:
//                         $n_announce_s = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
//                         $n_announce_s->execute([$n_user]);
//                         $n_announce_r = $n_announce_s->fetch();
//                         $announcement_id_a = $n_announce_r['current_location_id'];
//                         $all_read_sql = $pdo->prepare("DELETE FROM Announce_check WHERE user_id=? AND type=? AND current_location_id=?");
//                         $all_read_sql->execute([$_SESSION['user']['user_id'], $narrow, $announcement_id_a]);
//                         break;
//                     default:
//                         # code...
//                         break;
//                 }
//             }
//         }
//     }
// }
?>
<?php
require 'header.php';
?>
<link rel="stylesheet" href="mob_css/info-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/info.css" media="screen and (min-width: 1280px)">
<h1>お知らせ</h1>
<?php
// Announce_check参照
$list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
$list_sql->execute([$_SESSION['user']['user_id']]);
$list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
if ($list_raw) {
    ?>
    <form action="info.php" method="post">
        <label>種別</label>
        <select name="narrow">
            <option value=0 selected>全て</option>
            <option value=1>アナウンス</option>
            <option value=2>位置情報</option>
        </select>
        <label>ユーザー別</label>
        <select name="n_user">
            <option value=0 selected>全て</option>
            <?php
            $n_users = [];
            foreach ($list_raw as $row) {
                switch ($row['type']) {
                    case 1:
                        // アナウンス
                        $n_announcement_id = $row['announcement_id'];
                        $n_announce_s = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
                        $n_announce_s->execute([$n_announcement_id]);
                        $n_announce_r = $n_announce_s->fetch();
                        $n_send_person_id = $n_announce_r['send_person'];
                        $n_users[] = $n_send_person_id;
                        break;
                    case 2:
                        // 位置情報
                        $n_current_location_id = $row['current_location_id'];
                        $n_current_s = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=?');
                        $n_current_s->execute([$n_current_location_id]);
                        $n_current_r = $n_current_s->fetch();
                        $n_send_person_id = $n_current_r['user_id'];
                        $n_users[] = $n_send_person_id;
                    default:
                        # code...
                        break;
                }
            }
            $uni_n_users = array_unique($n_users);
            foreach ($uni_n_users as $n_user_r) {
                $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                $user_sql->execute([$n_user_r]);
                $user_row = $user_sql->fetch();
                echo '<option value=', $n_user_r, '>', $user_row['user_name'], '</option>';
                echo $n_user_r;
            }
            ?>
        </select>
        <input type="submit" value="検索" class="info">
    </form>
    <br>
    <form action="info.php" method="post">
        <?php
        if (isset($_POST['narrow'])) {
            echo '<input type="hidden" name="narrow" value=', $_POST['narrow'], '>';
        } else {
            echo '<input type="hidden" name="narrow" value=0>';
        }
        if (isset($_POST['n_user'])) {
            echo '<input type="hidden" name="n_user" value=', $_POST['n_user'], '>';
        } else {
            echo '<input type="hidden" name="n_user" value=0>';
        }
        ?>
        <input type="hidden" name="all_read">
        <input type="submit" value="一括既読" class="info">
    </form>
    <!-- <form action="info.php" method="post">
    <?php
    // if (isset($_POST['narrow'])) {
    //     echo '<input type="hidden" name="narrow" value=', $_POST['narrow'], '>';
    // } else {
    //     echo '<input type="hidden" name="narrow" value=0>';
    // }
    // if (isset($_POST['n_user'])) {
    //     echo '<input type="hidden" name="n_user" value=', $_POST['n_user'], '>';
    // } else {
    //     echo '<input type="hidden" name="n_user" value=0>';
    // }
    ?>
        <input type="hidden" name="all_delete">
        <input type="submit" value="一括削除" class="info">
    </form> -->
    <?php
    echo '<table>';
    if (isset($_POST['narrow'])) {
        $narrow = $_POST['narrow'];
    } else {
        $narrow = 0;
    }
    foreach ($list_raw as $row) {
        switch ($row['type']) {
            case 1:
                if ($narrow == 0 or $narrow == 1) {
                    $announcement_id = $row['announcement_id'];
                    $read_check = $row['read_check'];
                    $info_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
                    $info_sql->execute([$announcement_id]);
                    $info_row = $info_sql->fetch();
                    $send_id = $info_row['send_person'];
                    $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                    $user_sql->execute([$send_id]);
                    $user_row = $user_sql->fetch();
                    $send_name = $user_row['user_name'];
                    $title = $info_row['title'];
                    $content = $info_row['content'];
                    $logtime = $info_row['sending_time'];
                    if (isset($_POST['n_user']) && $_POST['n_user'] != 0) {
                        if ($send_id != $_POST['n_user']) {
                            continue 2; // 選択されたユーザー以外の通知はスキップ
                        }
                    }
                    echo '<tr>';
                    $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                    $iconStmt->execute([$send_id]);
                    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                    echo '<td>
                        <img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">
                        </td>';
                    echo '<td rowspan="1">', $send_name, 'さんから、アナウンスが届きました</td>';
                    if ($read_check == 0) {
                        echo '<td>未読</td>';
                    }
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td class="day">', timeAgo($logtime), '</td>';
                    echo '<td class="title"> 件名：', $title, '</td>';
                    ?>
                    <form action="info_detail.php" method="post">
                        <input type="hidden" name="announcement_id" value=<?php echo $announcement_id; ?>>
                        <td><input type="submit" value="詳細" class="edit"></td>
                    </form>
                    <?php
                }
                break;
            case 2:
                // 位置情報
                if ($narrow == 0 or $narrow == 2) {
                    $current_location_id = $row['current_location_id'];
                    $read_check = $row['read_check'];
                    $info_sql = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=?');
                    $info_sql->execute([$current_location_id]);
                    $info_row = $info_sql->fetch();
                    $send_id = $info_row['user_id'];
                    $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                    $user_sql->execute([$send_id]);
                    $user_row = $user_sql->fetch();
                    $send_name = $user_row['user_name'];
                    $logtime = $info_row['logtime'];
                    if (isset($_POST['n_user']) && $_POST['n_user'] != 0) {
                        if ($send_id != $_POST['n_user']) {
                            continue 2; // 選択されたユーザー以外の通知はスキップ
                        }
                    }
                    echo '<tr>';
                    $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                    $iconStmt->execute([$send_id]);
                    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                    echo '<td>
                        <img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">
                        </td>';
                    echo '<td rowspan="2">', $send_name, 'さんが位置情報を更新しました</td>';
                    if ($read_check == 0) {
                        echo '<td>未読</td>';
                    }
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td class="day">', timeAgo($logtime), '</td>';
                    ?>
                    <form action="info_detail.php" method="post">
                        <input type="hidden" name="current_location_id" value=<?php echo $current_location_id; ?>>
                        <td><input type="submit" value="詳細" class="edit"></td>
                    </form>
                    <?php
                }
                break;
            default:
                echo 'その他';
                break;
        }
    }
    echo '</table>';
} else {
    echo 'お知らせがありません';
}
?>