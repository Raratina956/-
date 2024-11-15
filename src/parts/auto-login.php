<?php
ob_start();
// セッションがまだ開始されていない場合に session_start() を呼び出す
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// 現在のURLを取得
$currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// URLをセッションに保存
$_SESSION['current_url'] = $currentUrl;

// セッションに保存されたURLを確認（デバッグ用）
// echo $_SESSION['current_url'];
// 現在のURLパスを取得
$requestUri = $_SERVER['REQUEST_URI'];

// クエリ部分を取り除いてファイル名だけを取得
$pathInfo = pathinfo($requestUri);
$fileName = $pathInfo['basename']; // ファイル名部分 (例: room.php)

// 'room.php' の場合に true の処理を行う
if ($fileName === 'room.php') {
    echo "room.php がリクエストされました。";
    // ここにtrueの処理を書く
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