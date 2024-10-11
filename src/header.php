<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/header.css">
    <link rel="stylesheet" href="css/test.css">
</head>
<header>

    <img src="img/icon.png" class="icon" width="300" height="80"> 
    <img src="img/bell.png" class="bell"width="50" height="50"> 
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
                <li>MAP</li>
                <li>ユーザー情報</li>
                <li>お気に入り</li>
                <li>QRカメラ</li>
                <li>チャット</li>
                <li><a href="tag_list.php">みんなのタグ</a></li>
                <li><a href="my_tag.php">MYタグ</a></li>
                <li>アナウンス</li>
                <li class="logout">ログアウト</li> <!-- ログアウトにクラスを追加 -->
            </ul>
        </div>
        <script>
        document.querySelector('.hamburger').addEventListener('click', function () {
            this.classList.toggle('active');
            document.querySelector('.slide-menu').classList.toggle('active');
        });
    </script>
</header>
