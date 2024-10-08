<?php
require 'parts/auto-login.php';
// require 'header.php';

$floor = $_POST['floor'];
echo '<h1>', $floor, '階</h1>';

$sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_floor = ?');
$sql->execute([$floor]);
$rows = $sql->fetchAll(PDO::FETCH_ASSOC);

$sql_current = $pdo->prepare('SELECT classroom_id, COUNT(*) AS user_count FROM Current_location GROUP BY classroom_id');
$sql_current->execute();
$results = $sql_current->fetchAll(PDO::FETCH_ASSOC);

echo '<ul>';
foreach ($rows as $row) {
    $classroom_id = $row['classroom_id'];
    $classroom_name = $row['classroom_name'];
    $user_count = 0;

    foreach ($results as $result) {
        if ($result['classroom_id'] == $classroom_id) {
            $user_count = $result['user_count'];
            break; 
        }
    }

    echo '<li>';
    echo '<a href="room.php?id=', $classroom_id, '">', $classroom_name, '　', $user_count, '人</a>';
    echo '</li>';
}
echo '</ul>';

