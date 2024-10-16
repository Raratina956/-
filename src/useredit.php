<?php 
    require 'parts/auto-login.php';
    require 'header.php';

    // ユーザー情報を「$_SESSION['user']['user_id』」を使って取得
    $users = $pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);

    // formを送る先を指定
    echo '<form id="uploadForm" action="useredit-output.php" method="post" enctype="multipart/form-data" class="clearfix">';

    foreach ($users as $user) {
        if ($user['s_or_t'] == 0) {
            // 生徒情報編集
            // アイコン編集
            echo '<div id="iconContainer">';  // 左側のアイコンコンテナ

            echo '<p>現在のアイコン</p>';  // アイコンの上にラベルを表示

            $iconStmt = $pdo->prepare('select * from Icon where user_id = ?');
            $iconStmt->execute([$_SESSION['user']['user_id']]);
            $icon = $iconStmt->fetch();

            if ($icon) {
                echo '<img id="existingIcon" src="', $icon['icon_name'], '" width="80%" height="auto" class="icon">';
            }

            echo '<input type="file" id="fileInput" name="icon_file" accept=".jpg"><br>';
            echo '<img id="preview" src="#" alt="Preview" style="display:none;"><br>';
            echo '<input type="hidden" name="user_id" value="', $_SESSION['user']['user_id'], '">';

            echo '</div>';  // 左側のコンテナを閉じる

            // 右側の名前・クラス選択を表示
            echo '<div id="formContainer">';  // 右側のコンテナ

            echo '名前：<input type="text" name="user_name" value="', $user['user_name'], '"><br>';
            
            // クラス変更
            echo '<label for="class">クラス：</label>';
            echo '<select name="class" id="class">';

            $classtagStmt = $pdo->prepare('select * from Classtag_attribute where user_id=?');
            $classtagStmt->execute([$_SESSION['user']['user_id']]);
            $classtag = $classtagStmt->fetch();

            $classtagnameStmt = $pdo->prepare('select * from Classtag_list where classtag_id=?');
            $classtagnameStmt->execute([$classtag['classtag_id']]);
            $classtagname = $classtagnameStmt->fetch();

            echo '<option value="', $classtag['classtag_id'], '" selected hidden>', $classtagname['classtag_name'], '</option>';

            $classStmt = $pdo->query('select * from Classtag_list');
            foreach ($classStmt as $class) {
                echo '<option value="', $class['classtag_id'], '">', $class['classtag_name'], '</option>';
            }

            echo '</select><br>';
            echo '<button type="button" id="uploadButton">保存</button>';
            echo '</div>';  // 右側のコンテナを閉じる
        }
    }
?>

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
</form>