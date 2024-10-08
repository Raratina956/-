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
    echo '<a herf="room.php?id=',$row['classroom_id'],'>';
    echo '<li>',$row['classroom_name'],'</li>';
    echo '</a>';
}
echo '</ul>';

