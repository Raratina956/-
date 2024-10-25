<?php
    require 'parts/auto-login.php';
?>

<!DOCTYPE html>
<html lang="en">

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
             <?php
               //ユーザー情報を持ってくる
                $users=$pdo->prepare('select * from Users where user_id=?');
                // $users->execute([$_SESSION['user']['user_id']]);
                $users->execute([$_GET['user_id']]);
                
                //アイコン情報を持ってくる
                $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
                $iconStmt->execute([$_GET['user_id']]);
                $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);

                echo '<ul>';
                //DBから持ってきたユーザー情報を「$user」に入れる
                    foreach($users as $user){
                        echo '<li><img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon"></li>';

                    }

             ?>

                <li>MAP</li>
                <li>ユーザー情報</li>
                <li>お気に入り</li>
                <li>QRカメラ</li>
                <li>チャット</li>
                <li>みんなのタグ</li>
                <li>MYタグ</li>
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