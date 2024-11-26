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
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    switch ($_POST['delete_type']) {
        case 1:
            $delete_sql = $pdo->prepare('DELETE FROM Announce_check WHERE announcement_id=?');
            break;
        case 2:
            $delete_sql = $pdo->prepare('DELETE FROM Announce_check WHERE current_location_id=?');
            break;
        case 3:
            $delete_sql = $pdo->prepare('DELETE FROM Announce_check WHERE message_id=?');
            break;
        default:
            # code...
            break;
    }
    $delete_sql->execute([$delete_id]);
}
if (isset($_POST['read_id'])) {
    $read_id = $_POST['read_id'];
    switch ($_POST['read_type']) {
        case 1:
            $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE announcement_id=?');
            break;
        case 2:
            $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE current_location_id=?');
            break;
        case 3:
            $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE message_id=?');
            break;
        default:
            # code...
            break;
    }
    $read_sql->execute([1, $read_id]);
}
if (isset($_POST['narrow'])) {
    $narrow = $_POST['narrow'];
    $narrow = intval($narrow);
} else {
    $narrow = 0;
}
// narrow→0:アナウンス、位置情報   1:アナウンス    2:位置情報   3:チャット
if (isset($_POST['n_user'])) {
    $n_user = $_POST['n_user'];
    $n_user = intval($n_user);
} else {
    $n_user = 0;
}
if (isset($message)) {
    unset($message);
}
// n_user→0:全てのユーザー  0以外:特定のユーザーID
// 一括既読機能
if (isset($_POST['all_read'])) {
    switch ($narrow) {
        case 0:
            switch ($n_user) {
                case 0:
                    // narrow:0 n_user:0の時
                    $all_read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=?');
                    $all_read_sql->execute([1, $_SESSION['user']['user_id']]);
                    $message = 'パターン１';
                    break;

                default:
                    // narrow:0 n_user:0以外の時
                    $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
                    $list_sql->execute([$_SESSION['user']['user_id']]);
                    $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
                    if ($list_raw) {
                        foreach ($list_raw as $list_row) {
                            if ($list_row['type'] == 1) {
                                // typeが1の場合(アナウンス)
                                $announce_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=? AND send_person=?');
                                $announce_sql->execute([$list_row['announcement_id'], $n_user]);
                                $announce_row = $announce_sql->fetch(PDO::FETCH_ASSOC);
                                if ($announce_row !== false) {
                                    $announcement_id_read = $announce_row['announcement_id'];
                                    $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND announcement_id=?');
                                    $read_sql->execute([1, $_SESSION['user']['user_id'], $announcement_id_read]);
                                    $message = 'パターン２';
                                }
                            } else if ($list_row['type'] == 2) {
                                // typeが2の場合(位置情報)
                                $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=? AND user_id=?');
                                $current_sql->execute([$list_row['current_location_id'], $n_user]);
                                $current_row = $current_sql->fetch(PDO::FETCH_ASSOC);
                                if ($current_row !== false) {
                                    $current_location_id_read = $current_row['current_location_id'];
                                    $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND current_location_id=?');
                                    $read_sql->execute([1, $_SESSION['user']['user_id'], $current_location_id_read]);
                                    $message = 'パターン２';
                                }
                            } else if ($list_row['type'] == 3) {
                                // typeが3の場合(チャット)
                                $mess_sql = $pdo->prepare('SELECT * FROM Message WHERE message_id=? AND send_id=?');
                                $mess_sql->execute([$list_row['message_id'], $n_user]);
                                $mess_row = $mess_sql->fetch(PDO::FETCH_ASSOC);
                                if ($mess_row !== false) {
                                    $message_id_read = $mess_row['message_id'];
                                    $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND message_id=?');
                                    $read_sql->execute([1, $_SESSION['user']['user_id'], $message_id_read]);
                                    $message = 'パターン２';
                                }

                            }
                        }
                    }
                    break;
            }
            break;
        case 1:
            switch ($n_user) {
                case 0:
                    // narrow:1 n_user:0の時
                    $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=?');
                    $read_sql->execute([1, $_SESSION['user']['user_id'], $narrow]);
                    $message = 'パターン3';
                    break;

                default:
                    // narrow:1 n_user:0以外の時
                    $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
                    $list_sql->execute([$_SESSION['user']['user_id']]);
                    $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
                    if ($list_raw) {
                        foreach ($list_raw as $list_row) {
                            $announce_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=? AND send_person=?');
                            $announce_sql->execute([$list_row['announcement_id'], $n_user]);
                            $announce_row = $announce_sql->fetch(PDO::FETCH_ASSOC);
                            if ($announce_row !== false) {
                                $announcement_id_read = $announce_row['announcement_id'];
                                $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND announcement_id=?');
                                $read_sql->execute([1, $_SESSION['user']['user_id'], $announcement_id_read]);
                                $message = 'パターン4';
                            }
                        }
                    }
                    break;
            }
            break;
        case 2:
            switch ($n_user) {
                case 0:
                    // narrow:2 n_user:0の時
                    $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=?');
                    $read_sql->execute([1, $_SESSION['user']['user_id'], $narrow]);
                    $message = 'パターン5';
                    break;

                default:
                    // narrow:2 n_user:0以外の時
                    $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
                    $list_sql->execute([$_SESSION['user']['user_id']]);
                    $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
                    if ($list_raw) {
                        foreach ($list_raw as $list_row) {
                            $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=? AND user_id=?');
                            $current_sql->execute([$list_row['current_location_id'], $n_user]);
                            $current_row = $current_sql->fetch(PDO::FETCH_ASSOC);
                            if ($current_row !== false) {
                                $current_location_id_read = $current_row['current_location_id'];
                                $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND current_location_id=?');
                                $read_sql->execute([1, $_SESSION['user']['user_id'], $current_location_id_read]);
                                $message = 'パターン6';
                            }
                        }
                    }
                    break;
            }
            break;
        case 3:
            switch ($n_user) {
                case 0:
                    // narrow:3 n_user:0の時
                    $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND type=?');
                    $read_sql->execute([1, $_SESSION['user']['user_id'], $narrow]);
                    $message = 'パターン5';
                    break;

                default:
                    // narrow:3 n_user:0以外の時
                    $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
                    $list_sql->execute([$_SESSION['user']['user_id']]);
                    $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
                    if ($list_raw) {
                        foreach ($list_raw as $list_row) {
                            $mess_sql = $pdo->prepare('SELECT * FROM Message WHERE message_id=? AND user_id=?');
                            $mess_sql->execute([$list_row['message_id'], $n_user]);
                            $mess_row = $mess_sql->fetch(PDO::FETCH_ASSOC);
                            if ($mess_row !== false) {
                                $message_id_read = $mess_row['message_id'];
                                $read_sql = $pdo->prepare('UPDATE Announce_check SET read_check=? WHERE user_id=? AND message_id=?');
                                $read_sql->execute([1, $_SESSION['user']['user_id'], $message_id_read]);
                                $message = 'パターン6';
                            }
                        }
                    }
                    break;
            }
            break;

    }
}
// n_user→0:全てのユーザー  0以外:特定のユーザーID
// 一括削除機能
if (isset($_POST['all_delete'])) {
    switch ($narrow) {
        case 0:
            switch ($n_user) {
                case 0:
                    // narrow:0 n_user:0の時
                    $all_read_sql = $pdo->prepare('DELETE FROM Announce_check WHERE user_id=?');
                    $all_read_sql->execute([$_SESSION['user']['user_id']]);
                    $message = 'パターン１';
                    break;

                default:
                    // narrow:0 n_user:0以外の時
                    $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
                    $list_sql->execute([$_SESSION['user']['user_id']]);
                    $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
                    if ($list_raw) {
                        foreach ($list_raw as $list_row) {
                            if ($list_row['type'] == 1) {
                                // typeが1の場合(アナウンス)
                                $announce_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=? AND send_person=?');
                                $announce_sql->execute([$list_row['announcement_id'], $n_user]);
                                $announce_row = $announce_sql->fetch(PDO::FETCH_ASSOC);
                                if ($announce_row !== false) {
                                    $announcement_id_read = $announce_row['announcement_id'];
                                    $read_sql = $pdo->prepare('DELETE FROM Announce_check WHERE user_id=? AND announcement_id=?');
                                    $read_sql->execute([$_SESSION['user']['user_id'], $announcement_id_read]);
                                    $message = 'パターン２';
                                }
                            } else if ($list_row['type'] == 2) {
                                // typeが2の場合(位置情報)
                                $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=? AND user_id=?');
                                $current_sql->execute([$list_row['current_location_id'], $n_user]);
                                $current_row = $current_sql->fetch(PDO::FETCH_ASSOC);
                                if ($current_row !== false) {
                                    $current_location_id_read = $current_row['current_location_id'];
                                    $read_sql = $pdo->prepare('DELETE FROM Announce_check WHERE user_id=? AND current_location_id=?');
                                    $read_sql->execute([$_SESSION['user']['user_id'], $current_location_id_read]);
                                    $message = 'パターン２';
                                }
                            }
                        }
                    }
                    break;
            }
            break;
        case 1:
            switch ($n_user) {
                case 0:
                    // narrow:1 n_user:0の時
                    $read_sql = $pdo->prepare('DELETE FROM Announce_check WHERE user_id=? AND type=?');
                    $read_sql->execute([$_SESSION['user']['user_id'], $narrow]);
                    $message = 'パターン3';
                    break;

                default:
                    // narrow:1 n_user:0以外の時
                    $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
                    $list_sql->execute([$_SESSION['user']['user_id']]);
                    $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
                    if ($list_raw) {
                        foreach ($list_raw as $list_row) {
                            $announce_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=? AND send_person=?');
                            $announce_sql->execute([$list_row['announcement_id'], $n_user]);
                            $announce_row = $announce_sql->fetch(PDO::FETCH_ASSOC);
                            if ($announce_row !== false) {
                                $announcement_id_read = $announce_row['announcement_id'];
                                $read_sql = $pdo->prepare('DELETE FROM Announce_check WHERE user_id=? AND announcement_id=?');
                                $read_sql->execute([$_SESSION['user']['user_id'], $announcement_id_read]);
                                $message = 'パターン4';
                            }
                        }
                    }
                    break;
            }
            break;
        case 2:
            switch ($n_user) {
                case 0:
                    // narrow:2 n_user:0の時
                    $read_sql = $pdo->prepare('DELETE FROM Announce_check WHERE user_id=? AND type=?');
                    $read_sql->execute([$_SESSION['user']['user_id'], $narrow]);
                    $message = 'パターン5';
                    break;

                default:
                    // narrow:2 n_user:0以外の時
                    $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
                    $list_sql->execute([$_SESSION['user']['user_id']]);
                    $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
                    if ($list_raw) {
                        foreach ($list_raw as $list_row) {
                            $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=? AND user_id=?');
                            $current_sql->execute([$list_row['current_location_id'], $n_user]);
                            $current_row = $current_sql->fetch(PDO::FETCH_ASSOC);
                            if ($current_row !== false) {
                                $current_location_id_read = $current_row['current_location_id'];
                                $read_sql = $pdo->prepare('DELETE FROM Announce_check WHERE user_id=? AND current_location_id=?');
                                $read_sql->execute([$_SESSION['user']['user_id'], $current_location_id_read]);
                                $message = 'パターン6';
                            }
                        }
                    }
                    break;
            }
            break;
    }
}
?>
<?php
require 'header.php';
?>
<link rel="stylesheet" href="mob_css/info-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/info.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
<link rel="stylesheet" href="css/info.css" media="screen and (min-width: 1280px)">
<!-- font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

<div class="center">
    <h1>お知らせ</h1>
    <?php
    // Announce_check参照
    $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
    $list_sql->execute([$_SESSION['user']['user_id']]);
    $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
    if ($list_raw) {
        ?>
        <?php
        // if (isset($message)) {
        //     echo '<p>';
        //     echo '<span>' . $message . '</span>';
        //     echo '</p>';
        // }
        ?>
        <div class="form-container">
            <form action="info.php" method="post" class="filter-form">
                <div class="form-row">
                    <label for="narrow">種別</label>
                    <select name="narrow" class="dropdown" id="narrow">
                        <option value=0 selected>全て</option>
                        <option value=1>アナウンス</option>
                        <option value=2>位置情報</option>
                        <option value=3>チャット</option>
                    </select>

                    <label for="n_user">ユーザー別</label>

                    <select name="n_user" class="dropdown" id="n_user">
                        <option value=0 selected>全て</option>
                        <?php
                        $n_users = [];
                        foreach ($list_raw as $row) {
                            switch ($row['type']) {
                                case 1:
                                    $n_announcement_id = $row['announcement_id'];
                                    $n_announce_s = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
                                    $n_announce_s->execute([$n_announcement_id]);
                                    $n_announce_r = $n_announce_s->fetch();
                                    $n_send_person_id = $n_announce_r['send_person'];
                                    $n_users[] = $n_send_person_id;
                                    break;
                                case 2:
                                    $n_current_location_id = $row['current_location_id'];
                                    $n_current_s = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=?');
                                    $n_current_s->execute([$n_current_location_id]);
                                    $n_current_r = $n_current_s->fetch();
                                    $n_send_person_id = $n_current_r['user_id'];
                                    $n_users[] = $n_send_person_id;
                                    break;
                                case 3:
                                    $n_message_id = $row['message_id'];
                                    $n_message_s = $pdo->prepare('SELECT * FROM Message WHERE message_id=?');
                                    $n_message_s->execute([$n_message_id]);
                                    $n_message_r = $n_message_s->fetch();
                                    $n_send_person_id = $n_message_r['send_id'];
                                    $n_users[] = $n_send_person_id;
                                default:
                                    # code...	}
                                    break;
                            }
                        }
                        $uni_n_users = array_unique($n_users);
                        foreach ($uni_n_users as $n_user_r) {
                            $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                            $user_sql->execute([$n_user_r]);
                            $user_row = $user_sql->fetch();
                            echo '<option value=', $n_user_r, '>', $user_row['user_name'], '</option>';
                        }
                        ?>
                    </select>

                    <input type="submit" value="検索" class="sort">
                </div>
            </form>

            <div class="action-buttons">
                <form action="info.php" method="post" class="inline-form">
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
                    <input type="submit" value="一括既読" class="read">
                </form>
                <form action="info.php" method="post" onsubmit="return confirmDelete()" class="inline-form">
                    <input type="hidden" name="all_delete">
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
                    <input type="submit" value="一括削除" class="delete">
                </form>
            </div>
        </div>



        <?php
        echo '<table>';
        if (isset($_POST['narrow'])) {
            $narrow = $_POST['narrow'];
        } else {
            $narrow = 0;
        }
        // 既存のリスト取得コード（例: $list_raw = 既存のデータ取得部分）
        // $list_raw = $pdo->query('SELECT * FROM Notification')->fetchAll(PDO::FETCH_ASSOC);
        // PHPで受信時間順に並べ替え（降順）
        // usort($list_raw, function ($a, $b) {
        //     // `sending_time`を日時として比較
        //     $time_a = strtotime($a['sending_time']);
        //     $time_b = strtotime($b['sending_time']);
        //     return $time_b - $time_a; // 降順でソート（新しいものを先に）
        // });
    

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
                        echo '<td style="width: 15%;"><a href="user.php?user_id=' . $send_id . '">';
                        echo '<img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">';
                        echo '</a></td>';
                        echo '<td width="15%"><img src="img/announce_info.png" width="40%" height="100%"></td>';
                        echo '<td colspan="3" style="width: 55%;">', $send_name, 'さんから、アナウンスが届きました</td>';
                        if ($read_check == 0) {
                            echo '<td style="width: 15%;">未読</td>';
                        } else {
                            echo '<td style="width: 15%;"></td>';
                        }
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td class="day" style="width: 10%;">', timeAgo($logtime), '</td>';
                        echo '<td colspan="2" class="title" style="width: 60%;"> 件名：', $title, '</td>';
                        ?>
                        <?php
                        if ($read_check == 0) {
                            ?>
                            <form action="info.php" method="post">
                                <input type="hidden" name="read_type" value=1>
                                <input type="hidden" name="read_id" value=<?php echo $announcement_id; ?>>
                                <td style="width: 10%;"><input type="submit" value="既読" class="read_one"></td>
                            </form>
                            <?php
                        } else {
                            echo '<td style="width: 10%;"></td>';
                        }
                        ?>
                        <form action="info_detail.php" method="post">
                            <input type="hidden" name="announcement_id" value=<?php echo $announcement_id; ?>>
                            <td style="width: 10%;"><input type="submit" value="詳細" class="edit"></td>
                        </form>
                        <form action="info.php" method="post" onsubmit="return confirmDelete()">
                            <input type="hidden" name="delete_type" value=1>
                            <input type="hidden" name="delete_id" value=<?php echo $announcement_id; ?>>
                            <td style="width: 10%;"><input type="submit" value="削除" class="delete_one"></td>
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
                        echo '<td style="width: 15%;"><a href="user.php?user_id=' . $send_id . '">';
                        echo '<img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">';
                        echo '</a></td>';
                        echo '<td width="15%"><img src="img/map_info.png" width="40%" height="100%"></td>';
                        echo '<td colspan="3" style="width: 55%;">', $send_name, 'さんが位置情報を更新しました</td>';
                        if ($read_check == 0) {
                            echo '<td style="width: 15%;">未読</td>';
                        } else {
                            echo '<td style="width: 15%;"></td>';
                        }
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td class="day" style="width: 10%;">', timeAgo($logtime);
                        ?>
                        <td colspan="2" style="width: 60%;"></td>
                        <?php
                        if ($read_check == 0) {
                            ?>
                            <form action="info.php" method="post">
                                <input type="hidden" name="read_type" value=2>
                                <input type="hidden" name="read_id" value=<?php echo $current_location_id; ?>>
                                <td style="width: 10%;"><input type="submit" value="既読" class="read_one"></td>
                            </form>
                            <?php
                        } else {
                            echo '<td style="width: 10%;"></td>';
                        }
                        ?>
                        <form action="info_detail.php" method="post">
                            <input type="hidden" name="current_location_id" value=<?php echo $current_location_id; ?>>
                            <td style="width: 10%;"><input type="submit" value="詳細" class="edit"></td>
                        </form>
                        <form action="info.php" method="post" onsubmit="return confirmDelete()">
                            <input type="hidden" name="delete_type" value=2>
                            <input type="hidden" name="delete_id" value=<?php echo $current_location_id; ?>>
                            <td style="width: 10%;"><input type="submit" value="削除" class="delete_one"></td>
                        </form>
                        <?php
                    }
                    break;
                case 3:
                    if ($narrow == 0 or $narrow == 3) {
                        $message_id = $row['message_id'];
                        $read_check = $row['read_check'];
                        $mess_sql = $pdo->prepare('SELECT * FROM Message WHERE message_id=?');
                        $mess_sql->execute([$message_id]);
                        $mess_row = $mess_sql->fetch(PDO::FETCH_ASSOC);
                        $send_id = $mess_row['send_id'];
                        $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                        $user_sql->execute([$send_id]);
                        $user_row = $user_sql->fetch();
                        $sent_name = $user_row['user_name'];
                        $logtime = $mess_row['message_time'];
                        if (isset($_POST['n_user']) && $_POST['n_user'] != 0) {
                            if ($send_id != $_POST['n_user']) {
                                continue 2; // 選択されたユーザー以外の通知はスキップ
                            }
                        }
                        echo '<tr>';
                        $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                        $iconStmt->execute([$send_id]);
                        $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                        echo '<td style="width: 15%;"><a href="user.php?user_id=' . $send_id . '">';
                        echo '<img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">';
                        echo '</a></td>';
                        echo '<td style="width: 15%;"><img src="img/chat_info.png" width="40%" height="100%"></td>';
                        echo '<td colspan="3" style="width: 55%;">', $sent_name, 'さんからチャットが届きました</td>';
                        if ($read_check == 0) {
                            echo '<td style="width: 15%;">未読</td>';
                        } else {
                            echo '<td style="width: 55%;"></td>';
                        }
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td class="day" style="width: 10%;">', timeAgo($logtime);
                        ?>
                        <td colspan="2" style="width: 60%;"></td>
                        <?php
                        if ($read_check == 0) {
                            ?>
                            <form action="info.php" method="post">
                                <input type="hidden" name="read_type" value=3>
                                <input type="hidden" name="read_id" value=<?php echo $message_id; ?>>
                                <td style="width: 10%;"><input type="submit" value="既読" class="read_one"></td>
                            </form>
                            <?php
                        } else {
                            echo '<td></td>';
                        }
                        ?>
                        <form action="info_detail.php" method="post">
                            <input type="hidden" name="message_id" value=<?php echo $message_id; ?>>
                            <td style="width: 10%;"><input type="submit" value="詳細" class="edit"></td>
                        </form>
                        <form action="info.php" method="post" onsubmit="return confirmDelete()">
                            <input type="hidden" name="delete_type" value=3>
                            <input type="hidden" name="delete_id" value=<?php echo $message_id; ?>>
                            <td style="width: 10%;"><input type="submit" value="削除" class="delete_one"></td>
                        </form>
                        <?php
                    }
                    break;
                default:
                    // echo 'その他';
                    break;
            }
        }
        echo '</table>';
    } else {
        echo 'お知らせがありません';
    }
    ?>
    <a href="map.php" class="back-link">戻る</a>
</div>
<script>
    function confirmDelete() {
        return confirm("本当に削除しますか？");
    }
</script>