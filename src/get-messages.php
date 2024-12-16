<?php
require 'database.php'; // DB接続ファイル

$partner_id = $_GET['partner_id'];
$logged_in_user_id = $_SESSION['user']['user_id'];

header('Content-Type: application/json');

if ($partner_id && $logged_in_user_id) {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME, USER, PASS);
    $sql = "SELECT message_id, send_id, message_detail, message_time 
            FROM Message 
            WHERE ((send_id = :logged_in_user_id AND sent_id = :partner_id)
               OR (send_id = :partner_id AND sent_id = :logged_in_user_id))
               AND already = 0
            ORDER BY message_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':logged_in_user_id', $logged_in_user_id, PDO::PARAM_INT);
    $stmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 既読フラグ更新
    $updateSql = "UPDATE Message 
                  SET already = 1 
                  WHERE sent_id = :logged_in_user_id AND send_id = :partner_id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->bindParam(':logged_in_user_id', $logged_in_user_id, PDO::PARAM_INT);
    $updateStmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
    $updateStmt->execute();

    echo json_encode($messages);
} else {
    echo json_encode(['error' => 'ユーザーIDが正しくありません']);
}
?>
