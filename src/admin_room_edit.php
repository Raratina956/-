<?php
session_start();
require 'parts/db-connect.php';
if (isset($_POST['classroom_id'])) {
    $class_id = $_POST['classroom_id'];
    if (isset($_POST['class_name']) && isset($_POST['class_floor'])) {
        $class_name = $_POST['class_name'];
        $class_floor = $_POST['class_floor'];
        $class_update = $pdo->prepare('UPDATE Classroom SET classroom_name = ? , classroom_floor = ? WHERE classroom_id = ?');
        $class_update ->execute([$class_name,$class_floor,$class_id]);
    } 
} else {
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/admin_room.php';
    header("Location: $redirect_url");
    exit();
}

$class_sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
$class_sql->execute([$class_id]);
$class_row = $class_sql->fetch();
$class_name = $class_row['classroom_name'];
$class_floor = $class_row['classroom_floor'];
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ページ</title>
</head>

<body>
    <p><span>教室編集</span></p>
    <form action="admin_room_edit.php" method="post">
        <input type="hidden" name="classroom_id" value=<?php echo $class_id; ?>>
        <input type="text" name="class_name" value="<?php echo $class_name; ?>" placeholder="教室名" required><br>
        <label for="class_floor">階数</label>
        <select id="class_floor" name="class_floor">
            <?php for ($i = 1; $i <= 7; $i++): ?>
                <option value="<?= $i ?>" <?= $i == $class_floor ? 'selected' : '' ?>><?= $i ?>階</option>
            <?php endfor; ?>
        </select>
        <input type="submit" value="変更">
    </form>
    <form action="admin_room.php" method="post">
        <input type="submit" value="戻る">
    </form>
</body>

</html>