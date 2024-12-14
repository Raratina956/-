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

// 以下通知処理
$current_datetime = date('Y-m-d H:i:s');

// ON DUPLICATE KEYを使用してデータを挿入または更新
$info_query = $pdo->prepare('
INSERT INTO Current_location (user_id, position_info_id, logtime) 
VALUES (?, ?, ?)
ON DUPLICATE KEY UPDATE 
    classroom_id = NULL,
    position_info_id = VALUES(position_info_id),
    logtime = VALUES(logtime)'
);
$info_query->execute([$user_id, 1, $current_datetime]);

// 主キーcurrent_location_idを取得
$current_location_id = $pdo->lastInsertId();

// 重複があった場合、lastInsertId()は0になるので、主キーを取得し直す
if ($current_location_id == 0) {
    $current_location_id_query = $pdo->prepare('SELECT current_location_id FROM Current_location WHERE user_id = ?');
    $current_location_id_query->execute([$user_id]);
    $current_location_id = $current_location_id_query->fetchColumn();
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
?>