<?php
if (isset($_POST['logout'])) {
    // ユーザー情報をセッションから削除
    unset($_SESSION['user']);

    // データベースからトークンを削除
    if (isset($_COOKIE['remember_me_token'])) {
        $token = $_COOKIE['remember_me_token'];

        // トークンをデータベースから削除
        $sql_delete_token = $pdo->prepare('DELETE FROM Login_tokens WHERE token = ?');
        $sql_delete_token->execute([$token]);
    }
    if (isset(($_SESSION['room']['uri']))) {
        unset($_SESSION['room']['uri']);
    }
    // クッキーを削除
    setcookie('remember_me_token', '', time() - 3600, "/"); // 過去の時間に設定

    // ログイン画面にリダイレクト
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/login.php';
    header("Location: $redirect_url");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mob_css/header-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" type="text/css" href="css/header.css" media="screen and (min-width: 1280px)">
    <link rel="stylesheet" href="mob_css/humberger-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/test.css" media="screen and (min-width: 1280px)">
    <link rel="icon" href="img/pin.png" sizes="32x32" type="image/png">
    <title>SpotLink</title>
</head>
<header>
    <div class="header-container">
        <a href="map.php" class="icon">
            <img src="img/icon.png" class="spot">
        </a>

        <div class="right-elements">
            <?php
            $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=? AND read_check=?');
            $list_sql->execute([$_SESSION['user']['user_id'], 0]);
            $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <a href="info.php" class="bell-icon">
                <img src="<?= $list_raw ? 'img/newinfo.png' : 'img/bell.png'; ?>" class="bell">
            </a>

            <div class="header-area">
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- スライドメニュー -->
    <div class="slide-menu">
        <!-- メニューリスト -->
        <?php
        //ユーザー情報を持ってくる
        $users = $pdo->prepare('select * from Users where user_id=?');
        // $users->execute([$_SESSION['user']['user_id']]);
        $users->execute([$_SESSION['user']['user_id']]);

        //アイコン情報を持ってくる
        $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
        $iconStmt->execute([$_SESSION['user']['user_id']]);
        $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
        $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE user_id = ?');
        $current_sql->execute([$_SESSION['user']['user_id']]);
        $current_row = $current_sql->fetch(PDO::FETCH_ASSOC);

        if ($current_row) {
            $class_id = $current_row['classroom_id'];
            $class_sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id = ?');
            $class_sql->execute([$class_id]);
            $class_row = $class_sql->fetch(PDO::FETCH_ASSOC);
            if ($class_row) {
                $class_name = $class_row['classroom_name'];
            } else {
                $class_name = 'クラス情報が見つかりません';
            }
        } else {
            $class_name = '設定なし';
        }

        echo '<ul>';
        //DBから持ってきたユーザー情報を「$user」に入れる
        foreach ($users as $user) {
            echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '"><img src="', $icon['icon_name'], '" width="50%" height="50%" class="usericon2"></a></li>';
            echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '">', $user['user_name'], '</a></li>';
        }
        ?>
        <li style="border-bottom: outset; border-color: #007bff5e;">現在地：　<?php echo $class_name; ?></li>
        <li style="border-bottom: outset; border-color: #007bff5e;">
        <form action="search.php" method="post">
            <input type="text" name="search" class="tbox" style="margin-top: 5%;width: 85%;text-align: center;" placeholder="ユーザー名 or タグ名"><br>
            <input type="submit" class="search1" value="検索" style="margin-bottom: 5%;margin-top: -5%;">
        </form>
        </li>

        <li style="border-bottom: outset; border-color: #007bff5e;"><a href="map.php">MAP</a></li>
        <li style="border-bottom: outset; border-color: #007bff5e;"><a href="favorite.php">お気に入り</a></li>
        <li style="border-bottom: outset; border-color: #007bff5e;"><a href="qr_read.php">QRカメラ</a></li>
        <?php echo '<li style="border-bottom: outset; border-color: #007bff5e;"><a href="chat-home.php?user_id=', $_SESSION['user']['user_id'], '">チャット</a></li>'; ?>
        <li style="border-bottom: outset; border-color: #007bff5e;"><a href="tag_list.php">みんなのタグ</a></li>
        <li style="border-bottom: outset; border-color: #007bff5e;"><a href="my_tag.php">MYタグ</a></li>
        <li style="border-bottom: outset; border-color: #007bff5e;"><a href="announce.php">アナウンス</a></li>
        <!-- 以下ログアウト -->
        <form id="myForm" action="" method="post">
            <input type="hidden" name="logout" value="1">
        </form>
        <li class="logout"><a href="#" id="submitLink">ログアウト</a></li>

        <script>
            document.getElementById('submitLink').addEventListener('click', function (event) {
                event.preventDefault(); // リンクのデフォルトの動作を防止
                // 現在のURLを取得
                var currentUrl = window.location.href;
                // フォームのactionに現在のURLを設定
                document.getElementById('myForm').action = currentUrl;
                // フォームを送信
                document.getElementById('myForm').submit();
            });
        </script>

        <!-- 以上ログアウト -->
        </ul>
    </div>
    <script>
        document.querySelector('.hamburger').addEventListener('click', function () {
            this.classList.toggle('active');
            document.querySelector('.slide-menu').classList.toggle('active');
        });
    </script>
</header>