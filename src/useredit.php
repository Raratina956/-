<?php
    require 'parts/auto-login.php';
    require 'header.php';

    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);

    //アイコン情報を持ってくる
    $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
    $iconStmt->execute([$_SESSION['user']['user_id']]);
    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);

    foreach($users as $user){
        //先生か生徒か判別
        if($user['s_or_t'] == 0){
            //生徒情報編集
            echo '名前：<input type="text" name="user_name" value="', $user['user_name'], '">';
            
            echo 'クラス：<input type="text" name="class" value="', $user['user_name'], '">';
        }
    }
?>