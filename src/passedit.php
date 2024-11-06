<?php
require 'parts/auto-login.php';
require 'header.php';

if (isset($_SESSION['err']['pass_err'])) {
    $error_message = $_SESSION['err']['pass_err'];
    echo '<script>alert("'.$error_message.'");</script>';
    // セッションエラーを消去 
    unset($_SESSION['err']['pass_err']);
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット</title>
    <!-- <link rel="stylesheet" href="mob_css/chat-mob.css" media="screen and (max-width: 480px)"> -->
    <link rel="stylesheet" href="css/passedit.css" media="screen and (min-width: 1280px)">
</head>
<div class="form">
    <form action="passedit_output.php" method="post" onsubmit="return confirm('本当に変更しますか？');">
        メールアドレス<input type="text" class="text" name="mail"><br>
        新規パスワード<input type="password" class="text" name="newpass"><br>
        確認パスワード<input type="password" class="text" name="re_newpass"><br>
        <button type="submit">登録</button>
    </form>
</div>
<button type="button" onclick="location.href='useredit.php'">戻る</button>
