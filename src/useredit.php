<?php
    require 'parts/auto-login.php';
    require 'header.php';

    if (isset($_SESSION['err']['success'])) {
        $error_message = $_SESSION['err']['success'];
        echo '<script>alert("'.$error_message.'");</script>';
        // セッションエラーを消去 
        unset($_SESSION['err']['success']);
    }

    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);

    //formを送る先を指定
    echo '<form id="uploadForm" action="useredit-output.php" method="post" enctype="multipart/form-data">';

    foreach($users as $user){
        //先生か生徒か判別
        if($user['s_or_t'] == 0){
            //生徒情報編集
            //アイコン編集

            echo '<div id="iconContainer">';  // 左側にアイコンを配置するためのコンテナ

            // 「現在のアイコン」ラベルを追加
            echo '<p>現在のアイコン</p>';

            $iconStmt = $pdo->prepare('select * from Icon where user_id = ?');
            $iconStmt->execute([$_SESSION['user']['user_id']]);
            $icon = $iconStmt->fetch();
            if ($icon) {
                echo '<img id="existingIcon" src="', $icon['icon_name'], '" width="20%" height="50%" class="icon">';
            }
?>
            <head>
            <!-- font -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

            <link rel="stylesheet" type="text/css" href="mob_css/useredit-mob.css" media="screen and (max-width: 480px)">
            <link rel="stylesheet" href="css/useredit.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
            <link rel="stylesheet" type="text/css" href="css/useredit.css" media="screen and (min-width: 1280px)">
            <input type="file" id="fileInput" name="icon_file" accept=".jpg, .png"><br>
            <img id="preview" src="#" alt="Preview" style="display:none;"><br>
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['user_id']; ?>">
            </div>
            </head>
            <body>

<?php
            echo '<div id="formContainer">';
            //名前変更
            echo '<label for="class">名前：</label><input type="text" name="user_name" value="', $user['user_name'], '"><br>';
?>
            <!-- クラス変更 -->
            <label for="class">クラス：</label>
            <select name="class" id="class">
                <?php
                    $classtagStmt=$pdo->prepare('select * from Classtag_attribute where user_id=?');
                    $classtagStmt->execute([$_SESSION['user']['user_id']]);
                    $classtag = $classtagStmt->fetch();

                    $classtagnameStmt=$pdo->prepare('select * from Classtag_list where classtag_id=?');
                    $classtagnameStmt->execute([$classtag['classtag_id']]);
                    $classtagname = $classtagnameStmt->fetch();

                    echo '<option value="', $classtag['classtag_id'], '" selected hidden>', $classtagname['classtag_name'], '</option>';


                    $classStmt=$pdo->query('select * from Classtag_list');
                    foreach($classStmt as $class){
                        echo '<option value="', $class['classtag_id'], '">', $class['classtag_name'], '</option>';
                    }
                ?>
            </select>
  <?php          
        }else if($user['s_or_t'] == 1){
            //教師情報編集
            //アイコン編集

            echo '<div id="iconContainer">';  // 左側にアイコンを配置するためのコンテナ

            // 「現在のアイコン」ラベルを追加
            echo '<p>現在のアイコン</p>';

            $iconStmt = $pdo->prepare('select * from Icon where user_id = ?');
            $iconStmt->execute([$_SESSION['user']['user_id']]);
            $icon = $iconStmt->fetch();
            if ($icon) {
                echo '<img id="existingIcon" src="', $icon['icon_name'], '" width="20%" height="50%" class="icon">';
            }
?>
            <link rel="stylesheet" type="text/css" href="mob_css/useredit-mob.css" media="screen and (max-width: 480px)">
            <link rel="stylesheet" type="text/css" href="css/useredit.css" media="screen and (min-width: 1280px)">
            <input type="file" id="fileInput" name="icon_file" accept=".jpg, .png"><br>
            <img id="preview" src="#" alt="Preview" style="display:none;"><br>
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['user_id']; ?>">
            </div>

<?php
            echo '<div id="formContainer">';
            //名前変更
            echo '<label for="class">名前：</label><input type="text" name="user_name" value="', $user['user_name'], '"><br>';
        }
    }
?>
    <button type="button" id="uploadButton">保存</button>
    </div>
    <button type="button" id="back" onclick="location.href='user.php?user_id=<?php echo $_SESSION['user']['user_id']; ?>'">戻る</button><br>
    <button type="button" id="pass" onclick="location.href='passedit.php'">パスワード変更</button>
    <script>
document.getElementById('fileInput').onchange = function (event) {
    var file = event.target.files[0];
    if (!file) {
        // ファイルが選択されていない場合 (キャンセルも含む)
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

document.getElementById('fileInput').oncancel = function () {
    // ファイル選択がキャンセルされた場合
    var defaultImage = 'img/icon/default.jpg';  // デフォルトの画像パスを指定
    var existingIcon = document.getElementById('existingIcon');
    var preview = document.getElementById('preview');
    if (existingIcon) {
        existingIcon.src = defaultImage;
    } else {
        preview.src = defaultImage;
        preview.style.display = 'block';
    }
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

    </form>
</body>


