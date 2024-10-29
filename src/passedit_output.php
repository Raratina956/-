<?php
require 'parts/auto-login.php';
require 'header.php';

    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);
    foreach($users as $user){
        if(password_verify($pass, $user['password'])){
            if($_POST['newpass'] == $_POST['re_newpass']){
                $hashed_password = password_hash($_POST['newpass'], PASSWORD_DEFAULT);

                $pass=$pdo->prepare('INSERT INTO Users(password) VALUES (?)');
                $pass->execute([$hashed_password]);

                $_SESSION['user'] = [
                    'success' => 'パスワード変更が完了しました'
                ];

                header("Location: useredit.php");
            }else{
                $_SESSION['user'] = [
                    'pass_err' => '新規パスワードと確認用パスワードが一致しません'
                ];
                
                header("Location: passedit.php");
            }
        }else{
            $_SESSION['user'] = [
                'pass_err' => '現在のパスワードが間違っています'
            ];

            header("Location: passedit.php");
        }
    
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }


?>