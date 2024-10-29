<?php
require 'parts/auto-login.php';

    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);

    foreach($users as $user){

        // 既存のパスワードと入力したパスワードが一致しているかを確認する
        if(password_verify($pass, $user['password'])){

            // 新規で入力したパスワードと確認用パスワードが一致しているかを確認する
            if($_POST['newpass'] == $_POST['re_newpass']){

                // 一致していた場合パスワードをハッシュ化して更新する
                $hashed_password = password_hash($_POST['newpass'], PASSWORD_DEFAULT);

                $pass=$pdo->prepare('UPDATE Users SET password=? WHERE user_id=?');
                $pass->execute([$hashed_password, $_SESSION['user']['user_id']]);

                $_SESSION['err'] = [
                    'success' => 'パスワード変更が完了しました'
                ];

                header("Location: useredit.php");
            }else{
                $_SESSION['err'] = [
                    'pass_err' => '新規パスワードと確認用パスワードが一致しません'
                ];
                
                header("Location: passedit.php");
            }
        }else{
            $_SESSION['err'] = [
                'pass_err' => '現在のパスワードが間違っています'
            ];

            header("Location: passedit.php");
        }
    }


?>