<?php require 'parts/auto-login.php'; ?>
<?php require 'header.php'; ?>
<?php
    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);
    
    //アイコン情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
    $iconStmt->execute([$_SESSION['user']['user_id']]);
    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);

    //DBから持ってきたユーザー情報を「$user」に入れる
    foreach($users as $user){
        //アイコン表示
        echo '<img src="', $icon['icon_name'], '">';

        //ユーザー名
        if($user['s_or_t'] == 0){
            //生徒(名前、クラス、メールアドレス)
            echo $user['user_name'];
            echo 'クラス：';
        }else{
            //先生
            echo $user['user_name'], "先生";
        }
    }

    //タグ情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $attribute=$pdo->prepare('select * from Tag_attribute where user_id=?');
    $attribute->execute([$_SESSION['user']['user_id']]);
    foreach($attribute as $tag_attribute){
        $tagStmt=$pdo->prepare('select * from Tag_list where tag_id=?');
        $tagStmt->execute([$tag_attribute['tag_id']]);
        echo 'a';

        //タグ一覧
        echo 'タグ一覧<br>';
        foreach($tagStmt as $tag){
            echo $tag['tag_name'];
        }
    }
?>