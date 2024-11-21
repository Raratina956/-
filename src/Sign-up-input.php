<?php
session_start();
require 'parts/db-connect.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規会員登録</title>
    <link rel="stylesheet" href="mob_css/sign-up-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/sign-up-input.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

</head>
<body>
    <h1>新規会員登録</h1>
    <form action="Sign-up-output.php" method="post">
        <div class="form-group">
            <label for="mail_address">メールアドレス：</label>
            <input type="email" name="mail_address" id="mail_address" maxlength="254" required>
        </div>
        <div class="form-group">
            <label for="type">分類：</label>
            <select name="type" id="type">
                <option value="0">学生</option>
                <option value="1">教師</option>
            </select>
        </div>
        <div class="form-group">
            <label for="name">名前：</label>
            <input type="text" name="name" id="name" maxlength="20" required>
        </div>
        <div class="form-group">
            <label for="password">パスワード：</label>
            <input type="password" name="password" id="password" maxlength="128" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">パスワード(確認用)：</label>
            <input type="password" name="confirm_password" id="confirm_password" maxlength="128" required>
        </div>
        <?php
        if (isset($_SESSION['login']['uperror'])) {
            $error = $_SESSION['login']['uperror'];
            echo '<div class="error"><span>' . $error . '</span></div>';
            unset($_SESSION['login']['uperror']);
        }
        ?>
        <br>
        <div class="button-group">
            <input type="submit" value="登録">
            <a class="back-link" href="login.php">戻る</a>
        </div>
    </form>
</body>
</html>
