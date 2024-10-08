<?php
require 'parts/auto-login.php';
?>

<?php
require 'header.php';
?>
<?php
$floor = $_POST['floor'];
echo '<h1>', $floor, '階</h1>';
$sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_floor = ?');
$sql->execute([$floor]);
$rows = $sql->fetchAll();
echo '<ul>';
foreach ($rows as $row) {
    echo '<li>';
    echo '<a herf="room.php?id=',$row['floor_id'],'>',$row['floor_name'],'</a>';
    echo '</li>';
}
echo '</ul>';

