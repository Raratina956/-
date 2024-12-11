<?php
session_start();
require 'parts/auto-login.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'データベース接続エラー: ' . $e->getMessage()]);
    exit();
}

// JSONデータを受け取る
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];

// 現在時刻を取得
$current_datetime = date('Y-m-d H:i:s');

// 現在の位置情報を検索
$info_search_sql = $pdo->prepare('SELECT * FROM Current_location WHERE user_id = ?');
$info_search_sql->execute([$user_id]);
$info_search_row = $info_search_sql->fetch();

if ($info_search_row) {
    // 既存のデータがあれば更新
    $get_primary_key = $pdo->prepare('SELECT id FROM Current_location WHERE user_id = ?');
    $get_primary_key->execute([$user_id]);
    $current_location_id = $get_primary_key->fetchColumn();
    $info_update = $pdo->prepare('UPDATE Current_location SET classroom_id = ?, position_info_id = ?, logtime = ? WHERE user_id = ?');
    $info_update->execute([null, 1, $current_datetime, $user_id]);

} else {
    // データがなければ挿入
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
        $announce_sql->execute([
            $favorite_row['follow_id'],
            2
        ]);
        if ($announce_sql->rowCount() == 0) {
            $new_announce = $pdo->prepare('INSERT INTO Announce_check(current_location_id, user_id, read_check, type) VALUES (?, ?, ?, ?)');
            $new_announce->execute([$current_location_id, $favorite_row['follow_id'], 0, 2]);
        } else {
            $update_announce = $pdo->prepare('UPDATE Announce_check SET current_location_id=?, read_check=? WHERE user_id=? AND type=?');
            $update_announce->execute([$current_location_id, 0, $favorite_row['follow_id'], 2]);
        }
    }
}


// 成功レスポンスを返す
echo json_encode(['success' => true, 'message' => '位置情報が更新されました']);
?>