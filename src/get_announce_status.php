<?php
session_start();
require 'parts/db-connect.php';// 必要に応じてDB接続コードをインクルード
$user_id = $_SESSION['user']['user_id'];

$sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id = ? AND read_check = 0');
$sql->execute([$user_id]);

$response = $sql->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'has_new_info' => !empty($response) // 新しいお知らせがあればtrue、なければfalse
]);
?>
