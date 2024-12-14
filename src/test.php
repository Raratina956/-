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