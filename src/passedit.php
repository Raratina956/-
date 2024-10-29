<?php
require 'parts/auto-login.php';
// require 'header.php';

if (isset($_SESSION['err']['pass_err'])) {
    $error_message = $_SESSION['err']['pass_err'];
    echo '<script>alert("'.$error_message.'");</script>';
    // セッションエラーを消去 
    unset($_SESSION['err']['pass_err']);
}
?>



<form action="passedit_output.php" method="post" onsubmit="return confirm('本当に変更しますか？');">
    メールアドレス<input type="text" name="mail"><br>
    新規パスワード<input type="password" name="newpass"><br>
    確認パスワード<input type="password" name="re_newpass"><br>
    <button type="submit">登録</button>
</form>
<button type="button" onclick="location.href='useredit.php'">戻る</button>
