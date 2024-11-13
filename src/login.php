<?php
session_start();
require 'parts/db-connect.php';
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
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/map.php';
    header("Location: $redirect_url");
    exit();
}
$error = '';
if (isset($_POST['mail_address'], $_POST['pass'])) {
    $mail = $_POST['mail_address'];
    $pass = $_POST['pass'];
    $sql = $pdo->prepare('SELECT * FROM Users WHERE mail_address=?');
    $sql->execute([$mail]);
    $row = $sql->fetch();
    if (!$row) {
        $error = 'メールアドレス又はパスワードが間違っています';
    } else {
        if ($row['s_or_t'] == 7) {
            $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/ban.php';
            header("Location: $redirect_url");
            exit();
        }
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user'] = [
                'user_id' => $row['user_id'],
                'user_name' => $row['user_name']
            ];

            // 自動ログイン処理開始
            // 自動ログインのためのクッキー設定
            if (isset($_POST['remember_me']) && $_POST['remember_me'] == 1) {
                // ランダムなトークン生成
                $token = bin2hex(random_bytes(16));
                $expires_at = date("Y-m-d H:i:s", strtotime('+30 days'));  // 30日後の日時

                // トークンをデータベースに保存
                $sql_insert_token = $pdo->prepare('INSERT INTO Login_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
                $sql_insert_token->execute([$row['user_id'], $token, $expires_at]);

                // クッキーを設定
                setcookie('remember_me_token', $token, time() + (86400 * 30), "/");  // 30日間有効
            }
            // 自動ログイン処理終了

            $now_time = date("Y/m/d H:i:s");
            $sql_update = $pdo->prepare('UPDATE Users SET last_login = ? WHERE user_id = ?');
            $sql_update->execute([
                $now_time,
                $row['user_id']
            ]);
            if($_SERVER['REQUEST_METHOD'] == 'GET'){
                $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/my_tag.php';
            header("Location: $redirect_url");
            exit();
            }
            $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/map.php';
            header("Location: $redirect_url");
            exit();
        } else {
            $error = 'メールアドレス又はパスワードが間違っています';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mob_css/login-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" type="text/css" href="css/login.css" media="screen and (min-width: 1280px)">
    <title>Document</title>
</head>

<body>
    <img src="img/icon2.png" alt="ロゴ" title="SpotLink">
    <form action="login.php" method="post">
        <br>
        <div class="form-group">
            <label for="mail_address">メールアドレス：</label>
            <input type="email" name="mail_address" id="mail_address" required>
        </div>
        <br>
        <div class="form-group">
            <label for="password">パスワード：</label>
            <input type="password" name="pass" id="password" required>
        </div>
        <br>
        <?php
        if (isset($_SESSION['login']['error'])) {
            $error = $_SESSION['login']['error'];
        }
        ?>
        <div class="error">
        <span><?php echo $error ?></span>
        </div>
        <br>
        <div class="next">
        <span>次回からログインを省略する</span>
        <br>
        <input type="checkbox" name="remember_me" value="1">
        </div>
        <br>
        <input type="submit" value="ログイン">
    </form>
    <a href="Sign-up-input.php">新規会員登録</a>
</body>

</html>