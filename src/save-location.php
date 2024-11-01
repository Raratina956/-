<?php
require 'db-connect.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // JSONデータを取得
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'];
    $latitude = $input['latitude'];
    $longitude = $input['longitude'];

    // 現在の日時を取得
    $updated_at = date('Y-m-d H:i:s');

    // データベースに挿入
    $stmt = $pdo->prepare("INSERT INTO location (user_id, latitude, longitude, updated_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE latitude = ?, longitude = ?, updated_at = ?");
    $stmt->execute([$user_id, $latitude, $longitude, $updated_at, $latitude, $longitude, $updated_at]);

    echo json_encode(['status' => 'success', 'message' => '位置情報が保存されました']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'データベース接続エラー: ' . $e->getMessage()]);
}
?>
