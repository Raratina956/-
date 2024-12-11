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
    $info_update = $pdo->prepare('UPDATE Current_location SET classroom_id = ?, position_info_id = ?, logtime = ? WHERE user_id = ?');
    $info_update->execute([null, 1, $current_datetime, $user_id]);
} else {
    // データがなければ挿入
    $info_insert = $pdo->prepare('INSERT INTO Current_location (user_id, position_info_id, logtime) VALUES (?, ?, ?)');
    $info_insert->execute([$user_id, 1, $current_datetime]);
}

// 成功レスポンスを返す
echo json_encode(['success' => true, 'message' => '位置情報が更新されました']);
?>
