<?php require 'parts/auto-login.php'; ?>
<?php require 'header.php'; ?>
<?php
    // echo '<script type="text/javascript" src="js/user.js"></script>';
    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);
    //$users->execute([$_POST['user_id']);  あとで変更予定
    
    //アイコン情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
    $iconStmt->execute([$_SESSION['user']['user_id']]);
    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);


    //DBから持ってきたユーザー情報を「$user」に入れる
    foreach($users as $user){

        //自分か相手側かで表示する内容を変更
        if($_SESSION['user']['user_id'] == ($user['user_id'])){
            //自分のプロフィール
            //アイコン表示
            echo '<img src="', $icon['icon_name'], '"><br>';

            //ユーザー情報
            if($user['s_or_t'] == 0){
                //生徒(名前、クラス、メールアドレス)
                echo $user['user_name'], '<br>';
                echo 'クラス：<br>';
                echo $user['mail_address'], '<br>';
            }else{
                //先生(名前、メールアドレス)
                echo $user['user_name'], "先生<br>";
                echo $user['mail_address'], '<br>';
            }

            //タグ情報を「$_SESSION['user']['user_id']」を使って持ってくる
            $attribute=$pdo->prepare('select * from Tag_attribute where user_id=?');
            $attribute->execute([$_SESSION['user']['user_id']]);
            $attributes = $attribute->fetchAll(PDO::FETCH_ASSOC);
            foreach($attributes as $tag_attribute){
                $tagStmt=$pdo->prepare('select * from Tag_list where tag_id=?');
                $tagStmt->execute([$tag_attribute['tag_id']]);
                $tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

                //タグ一覧
                echo 'タグ一覧<br>';
                foreach($tags as $tag){
                    echo $tag['tag_name'];
                }
            }
        }else{
            //相手のプロフィール
            //チャットボタン表示
            echo '<img src="img\chat.png"><br>';

            //お気に入りボタン表示
            $followStmt=$pdo->prepare('select * from Favorite where follow_id=?');
            $followStmt->execute([$_SESSION['user']['user_id']]);
            if(!empty($followStmt)){
                foreach($followStmt as $follow){
                    if($follow['follower_id'].equals($_POST['user_id'])){
                        echo '<img src="img\star.png"><br>';
                    }else{
                        echo '<img src="img\notstar.png"><br>';
                    }
                }
            }else{
                echo '<img src="img\notstar.png"><br>';
            }
            
            //アイコン表示
            echo '<img src="', $icon['icon_name'], '"><br>';

            //ユーザー情報
            if($user['s_or_t'] == 0){
                //生徒(名前、クラス、メールアドレス)
                echo $user['user_name'], '<br>';
                echo 'クラス：<br>';
                echo $user['mail_address'], '<br>';
            }else{
                //先生(名前、メールアドレス)
                echo $user['user_name'], "先生<br>";
                echo $user['mail_address'], '<br>';
            }

            //タグ情報を「$_SESSION['user']['user_id']」を使って持ってくる
            $attribute=$pdo->prepare('select * from Tag_attribute where user_id=?');
            $attribute->execute([$_SESSION['user']['user_id']]);
            $attributes = $attribute->fetchAll(PDO::FETCH_ASSOC);
            foreach($attributes as $tag_attribute){
                $tagStmt=$pdo->prepare('select * from Tag_list where tag_id=?');
                $tagStmt->execute([$tag_attribute['tag_id']]);
                $tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

                //タグ一覧
                echo 'タグ一覧<br>';
                foreach($tags as $tag){
                    echo $tag['tag_name'];
                }
            }
        }
    }
?>