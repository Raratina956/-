<?php
session_start();
require 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'];
    $friend_name = $data['friend_name'];

    try {
        // 友達リストに追加するためのユーザーIDを取得
        $stmt = $pdo->prepare('SELECT user_id FROM Users WHERE user_name = ?');
        $stmt->execute([$friend_name]);
        $friend = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($friend) {
            // 友達申請をデータベースに保存（友達申請テーブルを作成して保存する）
            $friend_id = $friend['user_id'];
            $insertStmt = $pdo->prepare('INSERT INTO FriendRequests (user_id, friend_id) VALUES (?, ?)');
            $insertStmt->execute([$user_id, $friend_id]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ユーザーが見つかりません']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'エラー: ' . $e->getMessage()]);
    }
}
?>
