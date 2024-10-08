<?php require 'parts/auto-login.php'; ?>
<?php require 'header.php'; ?>
<?php
    echo '<img src="">';
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);
    foreach($users as $user){   //DBから持ってきたユーザー情報を「$user」に入れる
        echo '<img src="', $user['icon_name'], '">';
    }
    echo 'ユーザーID：',$_SESSION['user']['user_id'];
    echo 'ユーザー名：',$_SESSION['user']['user_name'];
?>