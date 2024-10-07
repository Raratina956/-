<?php
session_start();
require 'db-connect.php';

$error = '';
if(isset($_POST['mail'],$_POST['pass'])){
    $mail = $_POST['mail'];
    $pass=$_POST['pass'];
    $sql = $pdo->prepare('SELECT * FROM user WHERE id=?');
    $sql->execute([$id]);
    $row = $sql->fetch();
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
        <span><?php echo $error?></span>
        <br>
        <span>次回からログインを省略する</span>
        <br>
        <input type="checkbox" name="remember_me" value="1">
        <br>
        <input type="submit" value="ログイン">
    </form>
</body>
</html>