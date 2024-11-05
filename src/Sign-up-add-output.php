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

try {
    // トランザクション開始
    $pdo->beginTransaction();
    $user_id = $_POST['user_id'];
    
    // 学籍番号の重複チェック
    $sql = "SELECT COUNT(*) FROM Users WHERE student_number = :student_number";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':student_number', $_POST['student_number']);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['login'] = [
            'number_error' => 'この学籍番号は既に存在します'
        ];
        $pdo->rollBack();
        echo '<form id="redirectForm" action="Sign-up-add-input.php" method="post">
                <input type="hidden" name="user_id" value="', $user_id, '">
              </form>';
        echo '<script>
                document.getElementById("redirectForm").submit();
              </script>';
        exit();
    }

    // ユーザーの更新
    $student_number = $_POST['student_number'];
    $sql = 'UPDATE Users SET student_number = :student_number WHERE user_id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':student_number', $student_number);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    // クラスタグの挿入
    $classtag_id = $_POST['class'];
    $sql = "INSERT INTO Classtag_attribute (classtag_id, user_id) VALUES (:classtag_id, :user_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':classtag_id', $classtag_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

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

    // コミット
    $pdo->commit();

    // リダイレクト
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/login.php';
    header("Location: $redirect_url");
    exit();

} catch (PDOException $e) {
    // エラーが発生した場合にトランザクションをロールバック
    $pdo->rollBack();
    echo "登録に失敗しました: " . $e->getMessage();
    exit();
}
?>
