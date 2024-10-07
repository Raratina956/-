<?php
session_start();
require 'db-connect.php';

$error = '';
if (isset($_POST['mail'], $_POST['pass'])) {
    $mail = $_POST['mail'];
    $pass = $_POST['pass'];
    $sql = $pdo->prepare('SELECT * FROM Users WHERE mail_address=?');
    $sql->execute([$mail]);
    $row = $sql->fetch();
    if (!$row) {
        $error = 'メールアドレス又はパスワードが間違っています';
    } else {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user'] = [
                'user_id' => $row['user_id '],
                'user_name' => $row['user_name']
            ];
            $now_time = date("Y/m/d H:i:s");
            $sql_update = $pdo->prepare('UPDATE Users SET last_login = ? WHERE user_id = ?');
            $sql_update->execute([
                $now_time,
                $row['user_id']
            ]);
            $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/main.php';
            header("Location: $redirect_url");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="login.php" method="post">
        <span>メールアドレス</span><input type="mail" name="mail" required>
        <br>
        <span>パスワード</span><input type="password" name="pass" required>
        <br>
        <span><?php echo $error ?></span>
        <br>
        <span>次回からログインを省略する</span>
        <br>
        <input type="checkbox" name="remember_me" value="1">
        <br>
        <input type="submit" value="ログイン">
    </form>
</body>

</html>