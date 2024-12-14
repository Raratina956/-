<?php
require 'parts/auto-login.php';
require 'header.php';

?>


<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="css/user.css" media="screen and (min-width: 1280px)">
    <link rel="stylesheet" type="text/css" href="css/user.css"
        media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" type="text/css" href="mob_css/user-mob.css" media="screen and (max-width: 480px)">

    <title>Document</title>

</head>

<body>
    <?php
    //フォロー・フォロワー機能
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $follower_id = $_POST['user_id'];
        $follow_id = $_SESSION['user']['user_id'];

        if (isset($_POST['action']) && $_POST['action'] == 'follow') {
            // フォローを追加
            $sql = $pdo->prepare('insert into Favorite (follow_id, follower_id) values (?, ?)');
            $sql->execute([$follow_id, $follower_id]);
        } elseif (isset($_POST['action']) && $_POST['action'] == 'unfollow') {
            // フォローを解除
            $sql = $pdo->prepare('delete from Favorite where follow_id=? and follower_id=?');
            $sql->execute([$follow_id, $follower_id]);
        }

        // リダイレクトして同じページを再読み込み
        header('Location: user.php?user_id=' . $_POST['user_id']);
        exit();
    }


    //ユーザー情報を持ってくる
    $users = $pdo->prepare('select * from Users where user_id=?');
    // $users->execute([$_SESSION['user']['user_id']]);
    $users->execute([$_GET['user_id']]);

    //アイコン情報を持ってくる
    $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
    $iconStmt->execute([$_GET['user_id']]);
    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);


    //DBから持ってきたユーザー情報を「$user」に入れる
    foreach ($users as $user) {

        //自分か相手側かで表示する内容を変更
        if ($_SESSION['user']['user_id'] == ($user['user_id'])) {
            //自分のプロフィール
            //アイコン表示
            echo '<div class="profile-container">';
            echo '<div class="user-container">';
            echo '<img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">';
            //ユーザー情報
            if ($user['s_or_t'] == 0) {

                // クラスを持ってくる
                $classtagStmt = $pdo->prepare('select * from Classtag_attribute where user_id=?');
                $classtagStmt->execute([$_SESSION['user']['user_id']]);
                $classtag = $classtagStmt->fetch();
                echo '<div class="profile">';
                // 生徒(名前、クラス、メールアドレス)
                echo '名前：', mb_substr($user['user_name'], 0, 10), "<br>";
                if ($classtag) {
                    $classtagnameStmt = $pdo->prepare('select * from Classtag_list where classtag_id=?');
                    $classtagnameStmt->execute([$classtag['classtag_id']]);
                    $classtagname = $classtagnameStmt->fetch();
                    echo 'クラス：', $classtagname['classtag_name'], '<br>';
                } else {
                    echo 'クラス：クラスが設定されていません', '<br>';
                }
                echo $user['mail_address'], "<br>";
                $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
                $current_sql->execute([$_SESSION['user']['user_id']]);
                $current_row = $current_sql->fetch();
                if ($current_row) {
                    $room_id = $current_row['classroom_id'];
                    $logtime = $current_row['logtime'];
                    $room_sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id =?');
                    $room_sql->execute([$room_id]);
                    $room_row = $room_sql->fetch();
                    $room_name = $room_row['classroom_name'];
                    echo '現在地：' . $room_name . '　';
                    echo timeAgo($logtime) . '登録<br>';
                } else {
                    echo '現在地：設定なし';
                }
                //編集ボタン
                echo '<button class="confirmbutton" onclick="location.href=\'useredit.php\'">編集</button>';
                echo '</div>';
            } else {
                //先生(名前、メールアドレス)
                echo '<div class="profile"><br>';
                echo '名前：', mb_substr($user['user_name'], 0, 10), "先生<br>";
                echo $user['mail_address'];
                //編集ボタン
                echo '<button class="confirmbutton" onclick="location.href=\'useredit.php\'">編集</button>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
            echo '<br>';
            //タグ情報を「$_SESSION['user']['user_id']」を使って持ってくる
            echo '<div class="tag">';
            $attribute = $pdo->prepare('select * from Tag_attribute where user_id=?');
            $attribute->execute([$_SESSION['user']['user_id']]);
            $attributes = $attribute->fetchAll(PDO::FETCH_ASSOC);

            echo 'タグ一覧<br><br>';
            foreach ($attributes as $tag_attribute) {
                $tagStmt = $pdo->prepare('select * from Tag_list where tag_id=?');
                $tagStmt->execute([$tag_attribute['tag_id']]);
                $tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

                //タグ一覧
                foreach ($tags as $tag) {
                    echo $tag['tag_name'];
                    echo '&emsp;';
                }

            }
            echo '</div>';
        } else {
            //相手のプロフィール
            $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id = ?');
            $user_sql->execute([$_GET['user_id']]);
            $user_row = $user_sql->fetch();
            $last_login = $user_row['last_login'];
            echo '<div class="profile-container">';
            echo '<span class="login-container">';
            // 最後のログイン時刻をもとに時間差を計算
            $timeAgoText = timeAgo($last_login);
            if (strpos($timeAgoText, '分前') !== false) {
                // "分前" が含まれる場合、数字を抽出して分数を取得
                preg_match('/(\d+)分前/', $timeAgoText, $matches);
                if (isset($matches[1]) && (int) $matches[1] <= 5) {
                    // 5分以内の場合は「オンライン中」と表示
                    echo '<span class="time_dis"><font color="#228b22">オンライン中</font></span>';
                } else {
                    // 通常の出力
                    echo '<span class="time_dis"><font color="#ff0000">' . $timeAgoText . 'にオンライン</font></span>';
                }
            } else {
                // 他の場合はそのまま出力
                echo '<span class="time_dis"><font color="#ff0000">' . $timeAgoText . 'にオンライン</font></span>';
            }
            echo '</span>';
            //チャットボタン表示
            echo '<div class="favorite-container">';
            echo '<button type="submit" class="star">';
            echo '<a href="https://aso2201203.babyblue.jp/Nomodon/src/chat.php?user_id=', $_GET['user_id'], '">';
            echo '<img src="img\chat.png" width="85%" height="100% class="chat">';
            echo '</a>';
            echo '</button>';

            //お気に入りボタン表示
            $followStmt = $pdo->prepare('select * from Favorite where follow_id=? and follower_id=?');
            $followStmt->execute([$_SESSION['user']['user_id'], $_GET['user_id']]);
            $follow = $followStmt->fetch();
            if ($follow) {
                echo '<form action="user.php" method="post">
                        <input type="hidden" name="user_id" value=', $_GET['user_id'], '>
                        <input type="hidden" name="action" value="unfollow">
                        <button type="submit" class="star">
                            <img src="img\star.png" width="85%" height="100%">
                        </button>
                      </form><br>';
            } else {
                echo '<form action="user.php" method="post">
                        <input type="hidden" name="user_id" value=', $_GET['user_id'], '>
                        <input type="hidden" name="action" value="follow">
                        <button type="submit">
                            <img src="img\notstar.png" width="85%" height="100%" class="star">
                        </button>
                      </form><br>';
            }
            echo '</div>';

            //アイコン表示
            echo '<div class="user-container">';
            echo '<img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon"><br>';

            echo '<div class="profile">';
            echo '名前：', $user['user_name'], "<br>";

            echo '</div>';
            echo '</div>';

            //タグ情報を「$_SESSION['user']['user_id']」を使って持ってくる
            echo '<div class="tag">';
            echo 'タグ一覧<br>';

            $attribute = $pdo->prepare('select * from Tag_attribute where user_id=?');
            $attribute->execute([$_GET['user_id']]);
            $attributes = $attribute->fetchAll(PDO::FETCH_ASSOC);
            foreach ($attributes as $tag_attribute) {
                $tagStmt = $pdo->prepare('select * from Tag_list where tag_id=?');
                $tagStmt->execute([$tag_attribute['tag_id']]);
                $tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

                //タグ一覧
                foreach ($tags as $tag) {
                    echo $tag['tag_name'];
                    echo '&emsp;';
                }

            }
            echo '</div>';
        }
    }
    ?>
    <!-- メイン(マップ)に戻る -->
    <button type="button" class="back" onclick="location.href='map.php'">戻る</button>
</body>

</html>

<?php
function timeAgo($logtime)
{
    if (empty($logtime)) {
        return '日時が設定されていません。'; // 空の日時に対する処理
    }
    $now = new DateTime(); // 現在時刻
    $ago = new DateTime($logtime); // 保存された日時
    $diff = $now->diff($ago); // 差分を取得

    // 差分を秒単位で計算
    $diffInSeconds = $now->getTimestamp() - $ago->getTimestamp();

    // 結果の判定
    if ($diffInSeconds < 3600) { // 1時間以内なら
        // 分数で表示
        $minutes = floor($diffInSeconds / 60);
        return $minutes . '分前';
    } elseif ($diffInSeconds < 86400) { // 24時間以内なら
        // 時間数で表示
        $hours = floor($diffInSeconds / 3600);
        return $hours . '時間前';
    } else {
        // 日数で表示
        $days = floor($diffInSeconds / 86400);
        return $days . '日前';
    }
}
?>