<?php
require 'db-connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];

try {
    $stmt = $pdo->prepare('REPLACE INTO locations (user_id, latitude, longitude) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $latitude, $longitude]);
    echo json_encode(['status' => 'success', 'message' => 'Location saved']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error saving location: ' . $e->getMessage()]);
}
?>
