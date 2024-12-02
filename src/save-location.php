<?php
require 'db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// 必要なデータが正しく渡されているか確認
if (isset($data['user_id'], $data['latitude'], $data['longitude'])) {
    $user_id = $data['user_id'];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];

    try {
        // `REPLACE INTO` の使用はデータの置き換えなので確認
        $stmt = $pdo->prepare('REPLACE INTO locations (user_id, latitude, longitude) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $latitude, $longitude]);

        // 正常に保存できた場合
        echo json_encode(['status' => 'success', 'message' => 'Location saved']);
    } catch (PDOException $e) {
        // エラーが発生した場合、詳細なメッセージを返す
        echo json_encode(['status' => 'error', 'message' => 'Error saving location: ' . $e->getMessage()]);
    }
} else {
    // 必要なデータが不足している場合のエラーメッセージ
    echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
}

?>
