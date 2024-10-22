<?php
session_start();
require 'parts/db-connect.php';

$error = '';
if (isset($_POST['mail_address'], $_POST['pass'])) {
    $mail = $_POST['mail_address'];
    $pass = $_POST['pass'];
    $sql = $pdo->prepare('SELECT * FROM Users WHERE mail_address=?');
    $sql->execute([$mail]);
    $row = $sql->fetch();
    if (!$row) {
        $error = 'メールアドレス又はパスワードが間違っています';
    } else {
        if($row['s_or_t']==7){
            $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/ban.php';
            header("Location: $redirect_url");
            exit();
        }
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user'] = [
                'user_id' => $row['user_id'],
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
        } else {
            $error = 'メールアドレス又はパスワードが間違っています';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <title>Document</title>
</head>

<body>
    <img src="img/icon2.png" alt="ロゴ" title="SpotLink">
    <form action="login.php" method="post">
        <br>
        <div class="form-group">
            <label for="mail_address">メールアドレス：</label>
            <input type="email" name="mail_address" id="mail_address" required>
        </div>
        <br>
        <div class="form-group">
            <label for="password">パスワード：</label>
            <input type="password" name="pass" id="password" required>
        </div>
        <br>
        <?php
        if (isset($_SESSION['login']['error'])) {
            $error = $_SESSION['login']['error'];
        }
        ?>
        <span><?php echo $error ?></span>
        <br>
        <span>次回からログインを省略する</span>
        <br>
        <input type="checkbox" name="remember_me" value="1">
        <br>
        <input type="submit" value="ログイン">
    </form>
    <a href="Sign-up-input.php">新規会員登録</a>
</body>

</html>