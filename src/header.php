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
    <link rel="stylesheet" href="css/header.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" type="text/css" href="css/header.css" media="screen and (min-width: 1280px)">
    <link rel="stylesheet" href="mob_css/humberger-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/test.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" href="css/test.css" media="screen and (min-width: 1280px)">
    <link rel="icon" href="img/pin.png" sizes="32x32" type="image/png">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=0.8"> -->
    <title>SpotLink</title>
</head>
<header>
    <div class="header-container">
        <a href="map.php" class="icon hover-effect-img">
            <img src="img/icon.png" class="spot">
        </a>
        <div class="right-elements">
            <?php
            $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=? AND read_check=?');
            $list_sql->execute([$_SESSION['user']['user_id'], 0]);
            $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <a href="info.php" class="bell-icon hover-effect-info">
                <img src="img/newinfo.png" class="bell">
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
        $current_slq_h = $pdo->prepare('SELECT * FROM Current_location WHERE user_id = ?');
        $current_slq_h->execute([$_SESSION['user']['user_id']]);
        $current_row_h = $current_slq_h->fetch(PDO::FETCH_ASSOC);

        if ($current_row_h) {
            if ($current_row_h['classroom_id']) {
                $class_id = $current_row_h['classroom_id'];
                $class_sql_h = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id = ?');
                $class_sql_h->execute([$class_id]);
                $class_row_h = $class_sql_h->fetch(PDO::FETCH_ASSOC);
                if ($class_row_h) {
                    $class_name_h = $class_row_h['classroom_name'];
                } else {
                    $class_name_h = '滞在情報が見つかりません';
                }
            } else if ($current_row_h['position_info_id']) {
                $class_name_h = '学外';
            }

        } else {
            $class_name_h = '設定なし';
        }

        echo '<ul>';
        //DBから持ってきたユーザー情報を「$user」に入れる
        foreach ($users as $user) {
            echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '"><img src="', $icon['icon_name'], '" width="50%" height="50%" class="usericon2"></a></li>';
            echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '">', $user['user_name'], '</a></li>';
        }
        ?>
        <li style="border-bottom: outset; border-color: #007bff5e;">現在地：　<?php echo $class_name_h; ?></li>
        <li style="border-bottom: outset; border-color: #007bff5e;">
            <form action="search.php" method="post">
                <input type="text" name="search" class="tbox" style="margin-top: 5%;width: 85%;text-align: center;"
                    placeholder="ユーザー名 or タグ名"><br>
                <input type="submit" class="search1" value="検索" style="margin-bottom: 5%;margin-top: -5%;">
            </form>
        </li>

        <style>
            .hover-effect {
                transition: transform 0.3s ease-in-out;
            }

            .hover-effect:hover {
                transform: scale(1.3);
            }

            .hover-effect-img {
                transition: transform 0.3s ease-in-out;
            }

            .hover-effect-img:hover {
                transform: scale(1.05);
            }

            .hover-effect-info {
                transition: transform 0.3s ease-in-out;
            }

            .hover-effect-info:hover {
                transform: scale(1.01);
            }
        </style>

        <li class="hover-effect" style="border-bottom: outset; border-color: #007bff5e;"><a href="map.php">MAP</a></li>
        <li class="hover-effect" style="border-bottom: outset; border-color: #007bff5e;"><a
                href="favorite.php">お気に入り</a></li>
        <li class="hover-effect" style="border-bottom: outset; border-color: #007bff5e;"><a href="qr_read.php">QRカメラ</a>
        </li>
        <li class="hover-effect" style="border-bottom: outset; border-color: #007bff5e;"><a
                href="chat-home.php?user_id=<?php echo $_SESSION['user']['user_id']; ?>">チャット</a></li>
        <li class="hover-effect" style="border-bottom: outset; border-color: #007bff5e;"><a
                href="tag_list.php">みんなのタグ</a></li>
        <li class="hover-effect" style="border-bottom: outset; border-color: #007bff5e;"><a href="my_tag.php">MYタグ</a>
        </li>
        <li class="hover-effect" style="border-bottom: outset; border-color: #007bff5e;"><a
                href="announce.php">アナウンス</a></li>

        <!-- 以下ログアウト -->
        <form id="myForm" action="" method="post">
            <input type="hidden" name="logout" value="1">
        </form>
        <li class="logout hover-effect"><a href="#" id="submitLink">ログアウト</a></li>

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

        document.addEventListener("DOMContentLoaded", () => {
            // document.body.style.transform = "scale(1)"; // ヘッダースクロール
            document.body.style.transformOrigin = "0 0";  // 左上を基準にズーム
        });
        document.addEventListener("DOMContentLoaded", () => {
            document.body.style.overflowX = "hidden"; // 横スクロールを無効化
        });

        // 通知アイコン自動変更
        function checkAnnounceStatus() {
            fetch('get_announce_status.php')
                .then(response => response.json())
                .then(data => {
                    const bellIcon = document.querySelector('.bell');
                    if (data.has_new_info) {
                        bellIcon.src = 'img/newinfo.png'; // 新しいお知らせがあればnewinfo.pngを表示
                    } else {
                        bellIcon.src = 'img/bell.png'; // お知らせがなければbell.pngを表示
                    }
                })
                .catch(error => console.error('Error fetching announcement status:', error));
        }

        // 5秒ごとにAPIを呼び出す
        setInterval(checkAnnounceStatus, 5000);

        // ページロード時にも一度実行
        checkAnnounceStatus();
    </script>
</header>