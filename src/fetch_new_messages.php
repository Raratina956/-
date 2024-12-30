<?php
require 'parts/auto-login.php';

try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "接続エラー: " . $e->getMessage()]);
    exit();
}

$logged_in_user_id = isset($_SESSION['user']['user_id']) ? $_SESSION['user']['user_id'] : null;
$partner_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$last_message_time = isset($_GET['last_message_time']) ? $_GET['last_message_time'] : null;

if ($logged_in_user_id === null || $partner_id === null || $last_message_time === null) {
    echo json_encode(["error" => "無効なリクエストです。"]);
    exit();
}

$sql = "SELECT message_id, send_id, sent_id, message_detail, message_time 
        FROM Message 
        WHERE ((send_id = :logged_in_user_id AND sent_id = :partner_id) 
           OR (send_id = :partner_id AND sent_id = :logged_in_user_id))
          AND message_time > :last_message_time
        ORDER BY message_time ASC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':logged_in_user_id', $logged_in_user_id, PDO::PARAM_INT);
$stmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
$stmt->bindParam(':last_message_time', $last_message_time);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
