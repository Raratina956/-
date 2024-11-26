<?php
require 'db-connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$sender_id = $data['sender_id'];
$receiver_id = $data['receiver_id'];

try {
    $stmt = $pdo->prepare('INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?, ?)');
    $stmt->execute([$sender_id, $receiver_id]);
    echo json_encode(['status' => 'success', 'message' => 'Friend request sent']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error sending friend request: ' . $e->getMessage()]);
}
?>
