<?php
require 'parts/auto-login.php';
try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "接続エラー: " . $e->getMessage();
    exit();
}

// ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
$users = $pdo->prepare('select * from Users where user_id=?');
$users->execute([$_SESSION['user']['user_id']]);

foreach ($users as $user) {
    $user_id = $_SESSION['user']['user_id'];
    $user_name = $_POST['user_name'];
    $sql = 'UPDATE Users SET user_name = :user_name WHERE user_id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_name', $user_name);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    if ($user['s_or_t'] == 0) {
        // 生徒更新
        $classtag_id = $_POST['class'];
        $sql = 'UPDATE Classtag_attribute SET classtag_id = :classtag_id WHERE user_id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':classtag_id', $classtag_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    // ファイルのアップロード
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['icon_file'])) {
        $uploadDir = 'img/icon/';
        $file = $_FILES['icon_file'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.jpg';  // 新しいファイル名を生成

        if ($fileExtension === 'png') {
            // PNG を JPG に変換
            $img = imagecreatefrompng($file['tmp_name']);
            $uploadFile = $uploadDir . $newFileName;
            imagejpeg($img, $uploadFile, 90);  // 90は品質
            imagedestroy($img);
        } else {
            // 直接アップロード
            $uploadFile = $uploadDir . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $uploadFile);
        }

        // データベースの更新
        $sql = 'UPDATE Icon SET icon_name = :icon_name WHERE user_id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':icon_name', $uploadFile);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    if ($stmt->execute()) {
        $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/user.php?user_id=' . $_SESSION['user']['user_id'];
        header("Location: $redirect_url");
        exit();
    } else {
        $error_info = $stmt->errorInfo();
        echo "登録に失敗しました: " . $error_info[2];
    }
}
?>
