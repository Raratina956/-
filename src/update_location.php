<?php
require 'db-connect.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // JSONデータを受け取る
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['user_id'], $input['latitude'], $input['longitude'])) {
        throw new Exception("無効なリクエストデータ");
    }

    $user_id = $input['user_id'];
    $latitude = $input['latitude'];
    $longitude = $input['longitude'];
    $updated_at = date('Y-m-d H:i:s');

    // データベースに位置情報を保存
    $stmt = $pdo->prepare("
        INSERT INTO locations (user_id, latitude, longitude, updated_at) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            latitude = VALUES(latitude), 
            longitude = VALUES(longitude), 
            updated_at = VALUES(updated_at)
    ");
    $stmt->execute([$user_id, $latitude, $longitude, $updated_at]);

    echo json_encode(['success' => true, 'message' => '位置情報が保存されました']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
    exit();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
echo json_encode("テスト");
