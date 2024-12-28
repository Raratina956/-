<?php
// get_data.php
header('Content-Type: application/json');

// データベース接続
const SERVER = 'mysql310.phy.lolipop.lan';
const DBNAME = 'LAA1516821-spotlink';
const USER = 'LAA1516821';
const PASS = 'nomodon';

$connect = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';
$pdo = new PDO($connect, USER, PASS);

// データを取得
$sql = "SELECT announce_check_id, message_id, announcement_id, current_location_id, user_id, read_check, type 
        FROM Announce_check 
        ORDER BY announce_check_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// JSON形式で返す
echo json_encode($data);
?>