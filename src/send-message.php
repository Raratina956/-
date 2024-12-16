<?php
require 'parts/db-connect.php'; // DB接続

// POSTデータを確認
if (!isset($_POST['partner_id']) || !isset($_POST['text'])) {
    echo json_encode(['error' => 'パラメータが不足しています']);
    exit;
}

$partner_id = $_POST['partner_id'];
$text = $_POST['text'];

// セッションのユーザー情報を確認
session_start();
if (!isset($_SESSION['user']['user_id'])) {
    echo json_encode(['error' => 'ログイン情報が確認できません']);
    exit;
}
$logged_in_user_id = $_SESSION['user']['user_id'];

try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // メッセージを挿入
    $stmt = $pdo->prepare("INSERT INTO Message (send_id, sent_id, message_detail, message_time, already) 
                            VALUES (:send_id, :sent_id, :message_detail, NOW(), 0)");
    $stmt->bindParam(':send_id', $logged_in_user_id, PDO::PARAM_INT);
    $stmt->bindParam(':sent_id', $partner_id, PDO::PARAM_INT);
    $stmt->bindParam(':message_detail', $text, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'メッセージ送信に失敗しました']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
