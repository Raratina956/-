<?php
require "parts/db-connect.php";
try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "接続エラー: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $mail_address = $_POST['mail_address'];
    $type = $_POST['type'];

    if ($password !== $confirm_password) {
        $_SESSION['login'] = [
            'uperror' => 'パスワードが一致しません。もう一度確認してください。'
        ];
        header("Location: Sign-up-input.php");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "SELECT COUNT(*) FROM Users WHERE mail_address = :mail_address";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':mail_address', $mail_address);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['login'] = [
            'uperror' => 'このメールアドレスは既に登録されています。別のメールアドレスを使ってください。'
        ];
        header("Location: Sign-up-input.php");
    } else {
        $sql = "INSERT INTO Users (mail_address, s_or_t, user_name, password) VALUES (:mail_address, :s_or_t, :user_name, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mail_address', $mail_address);
        $stmt->bindParam(':s_or_t', $type);
        $stmt->bindParam(':user_name', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->execute();

        $icon_name = "img/icon/default.jpg";
        $user_id = $pdo->lastInsertId();
        $sql = "INSERT INTO Icon (user_id, icon_name) VALUES (:user_id, :icon_name)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':icon_name', $icon_name);

        if ($stmt->execute()) {
            if($type == 0){
                echo '<form id="redirectForm" action="Sign-up-add-input.php" method="post">
                        <input type="hidden" name="user_id" value="', $user_id, '">
                      </form>';
                echo '<script>
                        document.getElementById("redirectForm").submit();
                      </script>';
            }else{
                $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/login.php';
                header("Location: $redirect_url");
                exit();
            }
        } else {
            $error_info = $stmt->errorInfo();
            echo "登録に失敗しました: " . $error_info[2];
        }
    }

}
?>