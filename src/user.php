<?php require 'parts/auto-login.php'; ?>
<?php require 'header.php'; ?>
<?php
    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);

    //アイコン情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $icon=$pdo->prepare('select icon_name from Icon where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);

    //DBから持ってきたユーザー情報を「$user」に入れる
    foreach($users as $user){
        echo '<img src="', $icon, '">';
    }
    echo 'ユーザーID：',$_SESSION['user']['user_id'];
    echo 'ユーザー名：',$_SESSION['user']['user_name'];
?>