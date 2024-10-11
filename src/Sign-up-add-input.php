<?php
require "parts/db-connect.php";
try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "接続エラー: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規会員登録</title>
    <link rel="stylesheet" href="css/sign-up-input.css">
</head>

<body>
    <h2>新規会員登録</h2>
    <form action="Sign-up-add-output.php" method="post">
        <div class="form-group">
            <label for="student_number">学籍番号：</label>
            <input type="email" name="student_number" id="student_number" required>
        </div>
        <div class="form-group">
            <label for="class">クラス：</label>
            <select name="class" id="class">
                <?php
                    $classStmt=$pdo->query('select * from Classtag_list');
                    foreach($classStmt as $class){
                        echo '<option value="', $class['classtag_id'], '">', $class['classtag_name'], '</option>';
                    }
                ?>
            </select>
        </div>
        <div class="form-group">
            <?php
                $iconStmt = $pdo->prepare('select * from Icon where user_id = ?');
                $iconStmt->execute([$_POST['user_id']]);
                $icon = $iconStmt->fetch();
                if ($icon) {
                    echo '<img id="existingIcon" src="', $icon['icon_name'], '" class="icon">';
                }
            ?>
        </div>
        <input type="file" id="fileInput" accept="image/*"><br>
        <img id="preview" src="#" alt="Preview" style="display:none; width=10%; height=10%"><br>

        <script>
            document.getElementById('fileInput').onchange = function (event) {
                var reader = new FileReader();
                reader.onload = function () {
                    var existingIcon = document.getElementById('existingIcon');
                    var preview = document.getElementById('preview');
                    
                    if (existingIcon) {
                        existingIcon.src = reader.result;  // 既存のアイコンを置き換える
                    } else {
                        preview.src = reader.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(event.target.files[0]);
            };
        </script>

        <input type="submit" value="登録">
    </form>
</body>

</html>