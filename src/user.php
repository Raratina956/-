<?php require 'parts/auto-login.php'; ?>
<?php require 'header.php'; ?>
<?php
    echo 'a';
    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);
    echo 'b';
    //アイコン情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
    $iconStmt->execute([$_SESSION['user']['user_id']]);
    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
    echo 'c';
    //DBから持ってきたユーザー情報を「$user」に入れる
    foreach($users as $user){
        echo 'd';
        echo '<img src="', $icon['icon_name'], '">';
        echo 'e';
    }
    echo 'ユーザーID：',$_SESSION['user']['user_id'];
    echo 'ユーザー名：',$_SESSION['user']['user_name'];
?>