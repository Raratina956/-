<?php
require 'db-connect.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // JSONデータ
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception("無効なJSON形式");
    }

    $user_id = $input['user_id'];
    $latitude = $input['latitude'];
    $longitude = $input['longitude'];
    $updated_at = date('Y-m-d H:i:s');

    
    $stmt = $pdo->prepare("INSERT INTO locations (user_id, latitude, longitude, updated_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE latitude = ?, longitude = ?, updated_at = ?");
    $stmt->execute([$user_id, $latitude, $longitude, $updated_at, $latitude, $longitude, $updated_at]);

    echo json_encode(['status' => 'success', 'message' => '位置情報が保存されました']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'データベース接続エラー: ' . $e->getMessage()]);
    exit(); 
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit(); 
}
