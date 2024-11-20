<?php
session_start();
require 'db-connect.php';

header('Content-Type: application/json');

// 自分のID
$selfUserId = 7;

// リクエストのデータを取得
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['latitude']) && isset($data['longitude'])) {
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];

    try {
        // データベースに位置情報を更新
        $updateLocationStmt = $pdo->prepare('
            UPDATE locations 
            SET latitude = ?, longitude = ?, updated_at = NOW() 
            WHERE user_id = ?
        ');
        $updateLocationStmt->execute([$latitude, $longitude, $selfUserId]);

        // 成功時のレスポンス
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // エラーレスポンス
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => '必要なデータが不足しています']);
}
?>
