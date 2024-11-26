<?php
require 'db-connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$request_id = $data['request_id'];
$status = $data['status'];

try {
    $stmt = $pdo->prepare('UPDATE friend_requests SET status = ? WHERE request_id = ?');
    $stmt->execute([$status, $request_id]);
    
    if ($status === 'accepted') {
        // 承認された場合、友達リストに追加する処理
    }

    echo json_encode(['status' => 'success', 'message' => 'Request processed successfully']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error processing request: ' . $e->getMessage()]);
}
?>
