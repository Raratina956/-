<?php
require 'db-connect.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // JSONデータを取得
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception("無効なJSON形式");
    }

    $user_id = $input['user_id'];
    $latitude = $input['latitude'];
    $longitude = $input['longitude'];
    $updated_at = date('Y-m-d H:i:s');

    // `locations` テーブルにデータを挿入または更新
    $stmt = $pdo->prepare("
        INSERT INTO locations (user_id, latitude, longitude, updated_at) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE latitude = ?, longitude = ?, updated_at = ?
    ");
    $stmt->execute([$user_id, $latitude, $longitude, $updated_at, $latitude, $longitude, $updated_at]);

    // 実行結果を確認
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => '位置情報が保存されました']);
    } else {
        echo json_encode(['status' => 'warning', 'message' => 'データが変更されませんでした']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'データベースエラー: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

