<?php
ob_start();
$current_file = basename(__FILE__);
// セッションがまだ開始されていない場合に session_start() を呼び出す
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if($current_file == "room.php"){
    echo 'A';
}else{
    echo $current_file;
}
require 'parts/db-connect.php';

// 自動ログイン処理開始
// クッキーのチェック
if (isset($_COOKIE['remember_me_token'])) {
    $token = $_COOKIE['remember_me_token'];

    // トークンを使ってユーザー情報を取得
    $sql = $pdo->prepare('SELECT * FROM Login_tokens WHERE token = ? AND expires_at > NOW()');
    $sql->execute([$token]);
    $login_token_row = $sql->fetch();

    if ($login_token_row) {
        // ユーザー情報を取得
        $sql_user = $pdo->prepare('SELECT * FROM Users WHERE user_id = ?');
        $sql_user->execute([$login_token_row['user_id']]);
        $user_row = $sql_user->fetch();

        if ($user_row) {
            $_SESSION['user'] = [
                'user_id' => $user_row['user_id'],
                'user_name' => $user_row['user_name']
            ];
        }
    }
}

// 自動ログイン処理終了

if (isset($_SESSION['login'])) {
    unset($_SESSION['login']);
}
if (!(isset($_SESSION['user']))) {
    $_SESSION['login']['error'] = 'ログインをしてください';
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/login.php';
    header("Location: $redirect_url");
    exit();
}
?>