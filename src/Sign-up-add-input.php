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
    <link rel="stylesheet" href="mob_css/student-mob1.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/sign-up-input.css" media="screen and (min-width: 1280px)">
</head>

<body>
    <h2>新規会員登録</h2>
    <form id="uploadForm" action="Sign-up-add-output.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="student_number">学籍番号：</label>
            <input type="text" name="student_number" id="student_number" maxlength="7" required placeholder="学籍番号は7桁で入力してください">
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
        <?php
            $iconStmt = $pdo->prepare('select * from Icon where user_id = ?');
            $iconStmt->execute([$_POST['user_id']]);
            $icon = $iconStmt->fetch();
            if ($icon) {
                echo '<img id="existingIcon" src="', $icon['icon_name'], '" class="icon">';
            }
        ?>
        <input type="file" id="fileInput" name="icon_file" class="image" accept=".jpg"><br>
        <img id="preview" src="#" alt="Preview" style="display:none;"><br>
        <input type="hidden" name="user_id" value="<?php echo $_POST['user_id']; ?>">
        <button type="button" id="uploadButton">登録</button>
    </form>

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

    document.getElementById('uploadButton').onclick = function () {
        var form = document.getElementById('uploadForm');
        if (form) {
            form.submit();
        } else {
            console.error('uploadForm not found');
        }
    };
</script>

</body>
</html>