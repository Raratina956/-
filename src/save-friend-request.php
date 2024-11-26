<?php
session_start();
require 'db-connect.php';
try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];
$friend_name = $data['friend_name'];

// ユーザーIDに一致する友達情報を取得
$stmt = $pdo->prepare('SELECT user_id FROM Users WHERE user_name = ?');
$stmt->execute([$friend_name]);
$friend = $stmt->fetch(PDO::FETCH_ASSOC);

if ($friend) {
    // 友達申請を保存
    $friend_id = $friend['user_id'];
    $stmt = $pdo->prepare('INSERT INTO FriendRequests (user_id, friend_id) VALUES (?, ?)');
    $stmt->execute([$user_id, $friend_id]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '友達が見つかりませんでした']);
}
