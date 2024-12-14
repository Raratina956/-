<?php
require 'parts/auto-login.php';
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $current_datetime = date('Y-m-d H:i:s');
    $info_search_sql = $pdo->prepare('SELECT * FROM Current_location WHERE user_id = ?');
    $info_search_sql->execute([$user_id]);
    $info_search_row = $info_search_sql->fetch();

    if ($info_search_row) {
        // データベース操作
        $info_update = $pdo->prepare('UPDATE Current_location SET classroom_id = ?, position_info_id = ?, logtime = ? WHERE user_id = ?');
        $info_update->execute([null, 1, $current_datetime, $user_id]);
        // 更新後にcurrent_location_idを取得
        $select_query = $pdo->prepare('SELECT current_location_id FROM Current_location WHERE user_id = ?');
        $select_query->execute([$user_id]);
        $current_location_id = $select_query->fetchColumn();
    } else {
        $info_insert = $pdo->prepare('INSERT INTO Current_location (user_id, position_info_id, logtime) VALUES (?, ?, ?)');
        $info_insert->execute([$user_id, 1, $current_datetime]);
        $current_location_id = $pdo->lastInsertId();
    }

    $favorite_user = $pdo->prepare('SELECT * FROM Favorite WHERE follower_id=?');
    $favorite_user->execute([$_SESSION['user']['user_id']]);
    $favorite_results = $favorite_user->fetchAll(PDO::FETCH_ASSOC);
    if ($favorite_results) {
        foreach ($favorite_results as $favorite_row) {
            $announce_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=? AND type=?');
            $announce_sql->execute([$favorite_row['follow_id'], 2]);
            if ($announce_sql->rowCount() == 0) {
                $new_announce = $pdo->prepare('INSERT INTO Announce_check(current_location_id, user_id, read_check, type) VALUES (?, ?, ?, ?)');
                $new_announce->execute([$current_location_id, $favorite_row['follow_id'], 0, 2]);
            } else {
                $update_announce = $pdo->prepare('UPDATE Announce_check SET current_location_id=?, read_check=? WHERE user_id=? AND type=?');
                $update_announce->execute([$current_location_id, 0, $favorite_row['follow_id'], 2]);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="test.php" method="post">
        <input type="hidden" name="user_id" value=4>
        <input type="submit" value="位置情報">
    </form>
</body>

</html>


//ユーザー情報
            if ($user['s_or_t'] == 0) {
                // クラスを持ってくる
                $classtagStmt = $pdo->prepare('select * from Classtag_attribute where user_id=?');
                $classtagStmt->execute([$_GET['user_id']]);
                $classtag = $classtagStmt->fetch();

                if ($classtag) {
                    $classtagnameStmt = $pdo->prepare('select * from Classtag_list where classtag_id=?');
                    $classtagnameStmt->execute([$classtag['classtag_id']]);
                    $classtagname = $classtagnameStmt->fetch();

                    // 生徒(名前、クラス、メールアドレス)
                    echo '<div class="profile">';
                    echo '名前：', $user['user_name'], "<br>";
                    echo 'クラス：', $classtagname['classtag_name'], '<br>';
                    echo $user['mail_address'], "<br>";
                    $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
                    $current_sql->execute([$_GET['user_id']]);
                    $current_row = $current_sql->fetch();
                    if ($current_row) {
                        if ($current_row['classroom_id']) {
                            $room_id = $current_row['classroom_id'];
                            $logtime = $current_row['logtime'];
                            $room_sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id =?');
                            $room_sql->execute([$room_id]);
                            $room_row = $room_sql->fetch();
                            $room_name = $room_row['classroom_name'];
                            echo '現在地：' . $room_name . '　';
                            echo timeAgo($logtime) . '登録<br>';
                        } else if ($current_row['position_info_id']) {
                            echo '現在地：学外　';
                            echo timeAgo($logtime) . '登録<br>';
                        }
                    } else {
                        echo '現在地：設定なし';
                    }
                    echo '</div>';
                } else {
                    // クラス情報がなかった場合の処理
                    echo '<div class="profile">';
                    echo '名前：', mb_substr($user['user_name'], 0, 10), "<br>";
                    echo 'クラス：クラスが設定されていません', '<br>';
                    echo $user['mail_address'], "<br>";
                    $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
                    $current_sql->execute([$_GET['user_id']]);
                    $current_row = $current_sql->fetch();
                    if ($current_row) {
                        if ($current_row['classroom_id']) {
                            $room_id = $current_row['classroom_id'];
                            $logtime = $current_row['logtime'];
                            $room_sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id =?');
                            $room_sql->execute([$room_id]);
                            $room_row = $room_sql->fetch();
                            $room_name = $room_row['classroom_name'];
                            echo '現在地：' . $room_name . '　';
                            echo timeAgo($logtime) . '登録<br>';
                        } else if ($current_row['position_info_id']) {
                            echo '現在地：学外　';
                            echo timeAgo($logtime) . '登録<br>';
                        }
                    } else {
                        echo '現在地：設定なし';
                    }
                    echo '</div>';
                }
            } else {
                //先生(名前、メールアドレス)
                echo '<div class="profile">';
                echo '名前：', mb_substr($user['user_name'], 0, 10), "先生<br>";
                echo $user['mail_address'], "<br>";
                $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
                $current_sql->execute([$_GET['user_id']]);
                $current_row = $current_sql->fetch();
                if ($current_row) {
                    if ($current_row['classroom_id']) {
                        $room_id = $current_row['classroom_id'];
                        $logtime = $current_row['logtime'];
                        $room_sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id =?');
                        $room_sql->execute([$room_id]);
                        $room_row = $room_sql->fetch();
                        $room_name = $room_row['classroom_name'];
                        echo '現在地：' . $room_name . '　';
                        echo timeAgo($logtime) . '登録<br>';
                    } else if ($current_row['position_info_id']) {
                        echo '現在地：学外　';
                        echo timeAgo($logtime) . '登録<br>';
                    }
                } else {
                    echo '現在地：設定なし';
                }
                echo '</div>';
            }