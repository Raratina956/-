<?php
    require 'parts/auto-login.php';

    //フォロー・フォロワー機能
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $follower_id = $_POST['user_id'];
        $follow_id = $_SESSION['user']['user_id'];

        if (isset($_POST['action']) && $_POST['action'] == 'follow') {
            // フォローを追加
            $sql = $pdo->prepare('insert into Favorite (follow_id, follower_id) values (?, ?)');
            $sql->execute([$follow_id, $follower_id]);
        } elseif (isset($_POST['action']) && $_POST['action'] == 'unfollow') {
            // フォローを解除
            $sql = $pdo->prepare('delete from Favorite where follow_id=? and follower_id=?');
            $sql->execute([$follow_id, $follower_id]);
        }

        // リダイレクトして同じページを再読み込み
        header('Location: user.php?user_id=' .$_POST['user_id']);
        exit();
    }

    require 'header.php';
    
    //ユーザー情報を持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    // $users->execute([$_SESSION['user']['user_id']]);
    $users->execute([$_GET['user_id']]);
    
    //アイコン情報を持ってくる
    $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
    $iconStmt->execute([$_GET['user_id']]);
    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);


    //DBから持ってきたユーザー情報を「$user」に入れる
    foreach($users as $user){

        //自分か相手側かで表示する内容を変更
        if($_SESSION['user']['user_id'] == ($user['user_id'])){
            //自分のプロフィール
            //アイコン表示
            echo '<img src="', $icon['icon_name'], '" width="10%" height="10%"><br>';

            //編集ボタン
            echo '<button onclick="location.href=\'useredit.php\'">編集</button>';


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
            echo '<img src="img\chat.png" width="10%" height="10%"><br>';

            //お気に入りボタン表示
            $followStmt=$pdo->prepare('select * from Favorite where follow_id=? and follower_id=?');
            $followStmt->execute([$_SESSION['user']['user_id'], $_GET['user_id']]);
            $follow = $followStmt->fetch();
            if($follow){
                echo '<form action="user.php" method="post">
                        <input type="hidden" name="user_id" value=', $_GET['user_id'], '>
                        <input type="hidden" name="action" value="unfollow">
                        <button type="submit">
                            <img src="img\star.png" width="10%" height="10%">
                        </button>
                      </form><br>';
            }else{
                echo '<form action="user.php" method="post">
                        <input type="hidden" name="user_id" value=', $_GET['user_id'], '>
                        <input type="hidden" name="action" value="follow">
                        <button type="submit">
                            <img src="img\notstar.png" width="10%" height="10%">
                        </button>
                      </form><br>';
            }

            //アイコン表示
            echo '<img src="', $icon['icon_name'], '" width="10%" height="10%"><br>';

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