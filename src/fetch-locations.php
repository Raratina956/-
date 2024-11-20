<?php
session_start();
require 'db-connect.php';

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 自分のユーザーIDを設定（例: 7）
    $user_id = 7;

    // 自分以外のユーザー情報を取得
    $stmt = $pdo->prepare('
        SELECT 
            Icon.user_id, 
            Icon.icon_name, 
            locations.latitude, 
            locations.longitude 
        FROM Icon
        INNER JOIN locations ON Icon.user_id = locations.user_id
        WHERE Icon.user_id != :user_id
    ');
    $stmt->execute(['user_id' => $user_id]);
    $allLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // JSONとしてデータを返す
    echo json_encode($allLocations);
} catch (PDOException $e) {
    echo json_encode(['error' => 'データベース接続エラー: ' . $e->getMessage()]);
}
?>
