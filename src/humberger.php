<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/test.css">
    <title>Document</title>
</head>

    <header>
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
                <li>みんなのタグ</li>
                <li>MYタグ</li>
                <li>アナウンス</li>
                <li>アナウンス</li>
                <li>アナウンス</li>
                <li>アナウンス</li>
                <li>アナウンス</li>
                <li>アナウンス</li>
                <li class="logout">ログアウト</li> <!-- ログアウトにクラスを追加 -->
            </ul>
        </div>
    </header>

    <script>
        document.querySelector('.hamburger').addEventListener('click', function () {
            this.classList.toggle('active');
            document.querySelector('.slide-menu').classList.toggle('active');
        });
    </script>

</html>