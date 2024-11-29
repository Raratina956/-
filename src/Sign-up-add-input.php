<?php
session_start();
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
    <link rel="stylesheet" href="css/sign-up-input.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" href="css/sign-up-input.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">
    <link rel="icon" href="img/pin.png" sizes="32x32" type="image/png">
    <title>SpotLink</title>
    <script>
        function validateForm() {
            var studentNumber = document.getElementById("student_number").value;
            if (studentNumber === "") {
                alert("学籍番号を入力してください。");
                return false;
            }
            return true;
        }

        function enableSubmitButton() {
            var studentNumber = document.getElementById("student_number").value;
            var submitButton = document.getElementById("uploadButton");
            if (/^\d{7}$/.test(studentNumber)) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("student_number").addEventListener("input", enableSubmitButton);
            enableSubmitButton(); // 初期ロード時にボタンの状態を設定
        });
    </script>
</head>
<body>
    <h2>新規会員登録</h2>
    <form id="uploadForm" action="Sign-up-add-output.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="student_number">学籍番号：</label>
            <input type="number" name="student_number" id="student_number" maxlength="7" placeholder="学籍番号は7桁で入力してください" pattern="\d{7}" required style="font-size: 15px;">
        </div>
        <div class="form-group">
            <label for="class">クラス：</label>
            <select name="class" id="class">
                <?php
                $classStmt = $pdo->query('select * from Classtag_list');
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
        <input type="file" class="file" id="fileInput" name="icon_file" accept=".jpg, .png"><br>
        <img id="preview" src="#" alt="Preview" style="display:none;"><br>
        <input type="hidden" name="user_id" value="<?php echo $_POST['user_id']; ?>">
        <?php
        if (isset($_SESSION['login']['number_error'])) {
            $error = $_SESSION['login']['number_error'];
            echo '<div class="error"><span>' . $error . '</span></div>';
            unset($_SESSION['login']['number_error']);
        }
        ?>
        <br>
        <button type="button" class="upload" id="uploadButton" disabled>登録</button>
    </form>
    <script>
        document.getElementById('fileInput').onchange = function (event) {
        var file = event.target.files[0];
        if (!file) {
            // ファイルが選択されていない場合
            var defaultImage = 'img/icon/default.jpg';  // デフォルトの画像パスを指定
            var existingIcon = document.getElementById('existingIcon');
            var preview = document.getElementById('preview');
            if (existingIcon) {
                existingIcon.src = defaultImage;
            } else {
                preview.src = defaultImage;
                preview.style.display = 'block';
            }
            return;
        }

        var reader = new FileReader();
        reader.onload = function () {
            var existingIcon = document.getElementById('existingIcon');
            var preview = document.getElementById('preview');
            var img = new Image();
            img.src = reader.result;
            img.onload = function () {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                // PNG を JPG に変換
                if (file.type === "image/png") {
                    ctx.drawImage(img, 0, 0);
                    var jpgDataUrl = canvas.toDataURL("image/jpeg", 0.9);
                    // プレビュー表示を更新
                    if (existingIcon) {
                        existingIcon.src = jpgDataUrl;
                    } else {
                        preview.src = jpgDataUrl;
                        preview.style.display = 'block';
                    }
                    // Data URL を Blob に変換してフォームに追加
                    var jpgBlob = dataURLtoBlob(jpgDataUrl);
                    var jpgFile = new File([jpgBlob], file.name.replace('.png', '.jpg'), { type: "image/jpeg" });
                    var dataTransfer = new DataTransfer();
                    dataTransfer.items.add(jpgFile);
                    document.getElementById('fileInput').files = dataTransfer.files;
                } else {
                    // 既存のプレビュー表示を更新
                    if (existingIcon) {
                        existingIcon.src = reader.result;
                    } else {
                        preview.src = reader.result;
                        preview.style.display = 'block';
                    }
                }
            };
        };
        reader.readAsDataURL(file);
    };

    // Data URL を Blob に変換する関数
    function dataURLtoBlob(dataurl) {
        var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    }

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
