<?php
    require 'parts/auto-login.php';
    try {
        $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "接続エラー: " . $e->getMessage();
        exit();
    }

    $user_id = $_SESSION['user']['user_id'];
    $user_name = $_POST['user_name'];
    $sql = 'UPDATE Users SET user_name = :user_name WHERE user_id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_name', $user_name);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    $classtag_id = $_POST['class'];
    $sql = 'UPDATE Classtag_attribute SET classtag_id = :classtag_id WHERE user_id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':classtag_id', $classtag_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['icon_file'])) {
        $uploadDir = 'img/icon/';
        $uploadFile = $uploadDir . basename($_FILES['icon_file']['name']);
        
        // ファイルを移動
        if (move_uploaded_file($_FILES['icon_file']['tmp_name'], $uploadFile)) {
            // データベースの更新
            $sql = 'UPDATE Icon SET icon_name = :icon_name WHERE user_id = :user_id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':icon_name', $uploadFile);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        }
    }

    if ($stmt->execute()) {
        $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/user.php?user_id=' . $_SESSION['user']['user_id'];
        header("Location: $redirect_url");
        exit();
        
    } else {
        $error_info = $stmt->errorInfo();
        echo "登録に失敗しました: " . $error_info[2];
    }
?>
