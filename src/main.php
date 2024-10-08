<?php
require 'parts/auto-login.php';
?>
ログイン後のページ
<?php
echo 'ユーザーID：',$_SESSION['user']['user_id'];
echo 'ユーザー名：',$_SESSION['user']['user_name'];
?>