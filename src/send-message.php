<?php
require 'database.php'; // DB接続ファイル

$partner_id = $_POST['partner_id'];
$logged_in_user_id = $_SESSION['user']['user_id'];
$message_detail = $_POST['text'];
$message_time = date('Y-m-d H:i:s');

header('Content-Type: application/json');

if ($partner_id && $logged_in_user_id && $message_detail) {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME, USER, PASS);
    $sql = "INSERT INTO Message (send_id, sent_id, message_detail, message_time, already) 
            VALUES (:send_id, :sent_id, :message_detail, :message_time, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':send_id', $logged_in_user_id, PDO::PARAM_INT);
    $stmt->bindParam(':sent_id', $partner_id, PDO::PARAM_INT);
    $stmt->bindParam(':message_detail', $message_detail, PDO::PARAM_STR);
    $stmt->bindParam(':message_time', $message_time, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'メッセージ送信に失敗しました']);
    }
} else {
    echo json_encode(['error' => '不正な入力です']);
}
?>
