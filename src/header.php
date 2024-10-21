<?php
if (isset($_POST['logout'])) {
    unset($_SESSION['user']);
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
    <!-- <link rel="stylesheet" type="text/css" href="css/header-mob.css" media="screen and (min-width: 480px)"> -->
    <link rel="stylesheet" type="text/css" href="css/header.css" media="screen and (min-width: 1280px)">
    <link rel="stylesheet" href="css/humberger-mob.css" media="screen and (min-width: 480px)">
    <link rel="stylesheet" href="css/test.css" media="screen and (min-width: 1280px)">
</head>
<header>

    <a href="main.php" class="icon"><img src="img/icon.png" width="460" height="80" class="spot"></a>
    <img src="img/bell.png" class="bell" width="50" height="50">
    <div class="header-area">
        <div class="hamburger">
            <!-- ハンバーガーメニューの線 -->
            <span></span>
            <span></span>
            <span></span>
            <!-- /ハンバーガーメニューの線 -->
        </div>
    </div>

    <!-- スライドメニュー -->
    <div class="slide-menu">
        <!-- メニューリスト -->
        <ul>
            <li><a href="map.php">MAP</a></li>
            <?php echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '">自分のプロフィール</a></li>'; ?>
            <li><a href="favorite.php">お気に入り</a></li>
            <li>QRカメラ</li>
            <li>チャット</li>
            <li><a href="tag_list.php">みんなのタグ</a></li>
            <li><a href="my_tag.php">MYタグ</a></li>
            <li>アナウンス</li>
            <!-- 以下ログアウト -->
            <form id="myForm" action="" method="post">
                <input type="hidden" name="logout" value="1">
            </form>
            <a href="#" id="submitLink">ログアウト</a>
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