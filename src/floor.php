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
$classroom_id = $rows['classroom_id'];
$classroom_name = $rows['classroom_name'];
$sql_current = $pdo->prepare('SELECT classroom_id, COUNT(*) AS user_count FROM Current_location GROUP BY classroom_id');
$sql_current->execute();
$results = $sql_current->fetchAll(PDO::FETCH_ASSOC);
echo '<ul>';
foreach ($results as $row) {
    echo '<li>';
    echo '<a href="room.php?id=',$classroom_id,'">',$classroom_name,'　',$row['user_count'],'人</a>';
    echo '</li>';
}
echo '</ul>';

