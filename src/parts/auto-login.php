<?php
ob_start();
// セッションがまだ開始されていない場合に session_start() を呼び出す
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// 現在のURLを取得
$requestUri = $_SERVER['REQUEST_URI'];
// URLのパス部分を抽出
$parsedUrl = parse_url($requestUri, PHP_URL_PATH);
// パス部分からファイル名だけを取得
$fileName = basename($parsedUrl);
// 'room.php' の場合に true の処理を行う
if (isset($_SESSION['room']['uri'])) {
    unset($_SESSION['room']['uri']);
}
if ($fileName === 'room.php' && !(isset($_COOKIE['remember_me_token']))) {
    $_SESSION['room']['uri'] = $requestUri;
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
} else {
    $now_time = date("Y/m/d H:i:s");
    $sql_update = $pdo->prepare('UPDATE Users SET last_login = ? WHERE user_id = ?');
    $sql_update->execute([
        $now_time,
        $_SESSION['user']['user_id']
    ]);
}
?>