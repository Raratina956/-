<?php
session_start();
include 'db_connection.php'; // データベース接続を行うコードを含めてください。

header('Content-Type: application/json');

if (!isset($_SESSION['user']['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user']['user_id'];

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) AS unread_count FROM Announce_check WHERE user_id = ? AND read_check = 0');
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'unread_count' => $result['unread_count'],
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
