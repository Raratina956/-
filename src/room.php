<?php
session_start();
require 'parts/auto-login.php';
require 'header.php'; // ヘッダー読み込み

function fix_url($url)
{
    return str_replace('&amp;', '&', $url);
}

// QRコードから読み取ったURLを修正
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $fixed_url = fix_url($_SERVER['REQUEST_URI']);
    parse_str(parse_url($fixed_url, PHP_URL_QUERY), $queryParams);
    $room_id = htmlspecialchars($queryParams['id']);
    $update_id = htmlspecialchars($queryParams['update']);
    $room=$pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
    $room->execute([$room_id]);
    $room_row=$room->fetch();

    if(!isset($_SESSION['floor']['kai'])){
        $_SESSION['floor'] = [
            'kai' => $room_row['classroom_floor']
        ];    
    }
} else {
    $room_id = $_GET['id'];
    $update_id = $_GET['update'];
}

$sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
$sql->execute([$room_id]);
$row = $sql->fetch();
$room_name = $row['classroom_name'];
$floor = $row['classroom_floor'];

// 位置情報を更新するかどうか確認（0 = 更新しない, 1 = 更新）
if ($update_id == 1) {
    // 位置情報を登録してるかどうか確認
    $now_time = date("Y/m/d H:i:s");
    $point = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
    $point->execute([$_SESSION['user']['user_id']]);
    if ($point->rowCount() == 0) {
        // 位置情報が未登録の場合の処理　→　新規登録
        $newpoint = $pdo->prepare('INSERT INTO Current_location(user_id, classroom_id, logtime) VALUES (?, ?, ?)');
        $newpoint->execute([$_SESSION['user']['user_id'], $room_id, $now_time]);
        $current_location_id = $pdo->lastInsertId();
    } else {
        // 位置情報が登録済の場合の処理　→　更新
        $updatepoint = $pdo->prepare('UPDATE Current_location SET classroom_id=?, logtime=? WHERE user_id=?');
        $updatepoint->execute([$room_id, $now_time, $_SESSION['user']['user_id']]);
        $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE classroom_id=? AND user_id=?');
        $current_sql->execute([$room_id, $_SESSION['user']['user_id']]);
        $current_row = $current_sql->fetch();
        $current_location_id = $current_row['current_location_id'];
    }

    $favorite_user = $pdo->prepare('SELECT * FROM Favorite WHERE follower_id=?');
    $favorite_user->execute([$_SESSION['user']['user_id']]);
    $favorite_results = $favorite_user->fetchAll(PDO::FETCH_ASSOC);
    if ($favorite_results) {
        foreach ($favorite_results as $favorite_row) {
            $announce_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=? AND type=?');
            $announce_sql->execute([
                $favorite_row['follow_id'],
                2
            ]);
            if ($announce_sql->rowCount() == 0) {
                $new_announce = $pdo->prepare('INSERT INTO Announce_check(current_location_id, user_id, read_check, type) VALUES (?, ?, ?, ?)');
                $new_announce->execute([$current_location_id, $favorite_row['follow_id'], 0, 2]);
            } else {
                $update_announce = $pdo->prepare('UPDATE Announce_check SET current_location_id=?, read_check=? WHERE user_id=? AND type=?');
                $update_announce->execute([$current_location_id, 0, $favorite_row['follow_id'], 2]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/room.css" media="screen and (min-width: 1280px)">
    <link rel="stylesheet" href="css/room.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" href="mob_css/room_mob.css" media="screen and (max-width: 480px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

    <title><?php echo htmlspecialchars($room_name); ?> - 位置登録</title>
</head>

<body>
    <main>
        <h1><?php echo htmlspecialchars($floor); ?>階</h1>
        <span><?php echo '<div class="heya">', htmlspecialchars($room_name), '</div>'; ?></span>
        <div  class="Current_container">
        <ul class="ul">
        <?php
        // 現在の位置情報を取得するクエリ
        $point = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
        $point->execute([$_SESSION['user']['user_id']]);
        $current_location = $point->fetch();
        if ($current_location && $current_location['classroom_id'] == $room_id) {
            echo '<div class="button-container"><button class="room" disabled><img  src="img/pin2.png" width="65px" height="60px" ><li>登録済み</li></button></div>';
        } else {
            if ($current_location) {
                echo '<div class="button-container"><form action="room.php?id=' . htmlspecialchars($room_id) . '&update=1" method="post">
                        <input type="hidden" name="judge" value="1">  <!-- 更新のためのフラグ -->
                        <li><input class="room" type="image" src="img/pin.png" width="65px" height="60px" value="位置情報を更新"></li>
                        <li>更新</li>
                      </form></div>';
            } else {
                echo '<div class="button-container"><form action="room.php?id=' . htmlspecialchars($room_id) . '&update=1" method="post">
                        <input type="hidden" name="judge" value="0">
                        <li><input class="room" type="submit" value="位置登録"></li>
                        
                      </form></div>';
            }
        }
        ?>
      
        <!-- QR表示 -->
        <form id="qr-form" action="qr_show.php" method="post" target="_blank">
            <?php    
                echo '<input type="hidden" name="room" value="', htmlspecialchars($room_name), '">';
                echo '<input type="hidden" name="custom_url" value="https://aso2201203.babyblue.jp/Nomodon/src/room.php?id=' . htmlspecialchars($room_id) . '&update=1">';
            ?>
             <li><input class="room" type="image" src="img/QR.png" width="70px" height="70px" value="QR"></li>
             <li>QR</li>
        </form>
        </ul>
    </div>
    

        <!-- 教室にいるユーザーを表示 -->
        <?php
            // 表示するユーザーの絞り込み
            $target = isset($_POST['target']) ? $_POST['target'] : 'all';
            $favorite = isset($_POST['favorite']) ? $_POST['favorite'] : 0;
            
            echo '<form action="room.php?id=' . htmlspecialchars($room_id) . '&update=0" method="post">
                    <select name="target">
                        <option value="all"' . ($target === 'all' ? ' selected' : '') . '>全て</option>
                        <option value="teacher"' . ($target === 'teacher' ? ' selected' : '') . '>教師</option>
                        <option value="student"' . ($target === 'student' ? ' selected' : '') . '>生徒</option>
                    </select>
                    <select name="favorite">
                        <option value="0"' . ($favorite == 0 ? ' selected' : '') . '>全てのユーザー</option>
                        <option value="1"' . ($favorite == 1 ? ' selected' : '') . '>お気に入り登録中</option>
                        </select>
                        <button type="submit">検索</button>
                  </form>';
            
            echo '<form action="floor.php" method="post">';
                echo '<input type="hidden" name="floor" value="', $_SESSION['floor']['kai'], '">';
                echo '<button type="submit" class="back-link">戻る</button>';
            echo '</form>'; 

            echo '<div class="icon_container">';
            echo '<ul class="flex_item">';

            // 初期分岐と「すべて」選択時
            if(empty($_POST['target']) || $_POST['target'] == "all"){

                // 初期分岐と未選択時
                if(empty($_POST['favorite']) || $_POST['favorite'] == 0) {
                    // 教室にいるメンバーを持ってくる(全件表示)
                    $users = $pdo->prepare('SELECT * FROM Current_location WHERE classroom_id=?');
                    $users->execute([$room_id]);
                    $usersList = $users->fetchAll(PDO::FETCH_ASSOC);
                
                    // ユーザーがいるかどうか
                    if ($usersList) {
                        // 初期表示、全件表示
                        foreach($usersList as $user) {
                            // ユーザー情報を持ってくる
                            $members = $pdo->prepare('select * from Users where user_id=?');
                            $members->execute([$user['user_id']]);
                            $member = $members->fetch(PDO::FETCH_ASSOC);
                
                            // アイコン情報を持ってくる
                            $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                            $iconStmt->execute([$user['user_id']]);
                            $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                
                            echo '<li class="li" style="list-style: none; padding-left: 0;">
                                    <div class="profile-container"><div class="user-container">
                                    <img src="', htmlspecialchars($icon['icon_name']), '"  class="usericon">
                                    <span><a href="user.php?user_id=' . htmlspecialchars($user['user_id']) . '">', htmlspecialchars($member['user_name']) ,'</a></span>
                                  </li>';
                        }
                    } else {
                        echo '<p>ユーザーが見つかりませんでした。</p>';
                    }

                // お気に入り選択時
                }else if($_POST['favorite'] == 1) {
                    // お気に入り登録しているユーザーのidを持ってくる
                    $favorites = $pdo->prepare('SELECT * FROM Favorite where follow_id=?');
                    $favorites->execute([$_SESSION['user']['user_id']]);
                    $found = false; // ユーザーが見つかったかどうかを示すフラグ
            
                    foreach($favorites as $favorite) {
                        // 教室にいるメンバーを持ってくる(全件表示)
                        $users = $pdo->prepare('SELECT * FROM Current_location WHERE classroom_id=? AND user_id=?');
                        $users->execute([$room_id, $favorite['follower_id']]);
                        
                        foreach($users as $user) {
                            $found = true; // ユーザーが見つかった場合にフラグを設定
                
                            // ユーザー情報を持ってくる
                            $members = $pdo->prepare('select * from Users where user_id=?');
                            $members->execute([$user['user_id']]);
                            $member = $members->fetch(PDO::FETCH_ASSOC);
                
                            // アイコン情報を持ってくる
                            $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                            $iconStmt->execute([$user['user_id']]);
                            $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                
                            echo '<li style="list-style: none; padding-left: 0;">
                                    <div class="profile-container"><div class="user-container">
                                    <img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">
                                    <a href="user.php?user_id=' . $user['user_id'] . '">', $member['user_name'] ,'</a>
                                </li>';
                        }
                    }
                    
                    if (!$found) {
                        // 条件に合うユーザーが見つからなかった場合のメッセージ
                        echo "ユーザーが見つかりませんでした。";
                    }
                }

            //教師選択時
            }else if($_POST['target'] == "teacher") {
                // 初期分岐と未選択時
                if(empty($_POST['favorite']) || $_POST['favorite'] == 0) {
                    // 教室にいるメンバーを持ってくる(全件表示)
                    $users = $pdo->prepare('
                        SELECT Users.* FROM Users
                        JOIN Current_location ON Users.user_id = Current_location.user_id
                        WHERE Current_location.classroom_id = ? AND Users.s_or_t = 1
                    ');
                    $users->execute([$room_id]);
                    $usersList = $users->fetchAll(PDO::FETCH_ASSOC);
            
                    // ユーザーがいるかどうか
                    if ($usersList) {
                        // 初期表示、全件表示
                        foreach($usersList as $user) {

                            // ユーザー情報を持ってくる
                            $members = $pdo->prepare('select * from Users where user_id=?');
                            $members->execute([$user['user_id']]);
                            $member = $members->fetch(PDO::FETCH_ASSOC);

                            // アイコン情報を持ってくる
                            $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                            $iconStmt->execute([$user['user_id']]);
                            $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
            
                            echo '<li style="list-style: none; padding-left: 0;">
                                    <div class="profile-container"><div class="user-container">
                                    <img src="'. htmlspecialchars($icon['icon_name']). '" width="20%" height="50%" class="usericon">
                                   <span><a href="user.php?user_id=' . htmlspecialchars($user['user_id']) . '">', htmlspecialchars($member['user_name']) .'</a></span>
                                  </li>';
                        }
                    } else {
                        // 条件に合うユーザーが見つからなかった場合のメッセージ
                        echo '<p>ユーザーが見つかりませんでした。</p>';
                    }
            
                // お気に入り選択時
                } else if($_POST['favorite'] == 1) {
                    // 教室にいるメンバーを持ってくる(お気に入りに登録している場合)
                    $users = $pdo->prepare('
                        SELECT Users.* FROM Users
                        JOIN Current_location ON Users.user_id = Current_location.user_id
                        JOIN Favorite ON Users.user_id = Favorite.follower_id
                        WHERE Current_location.classroom_id = ? AND Users.s_or_t = 1 AND Favorite.follow_id = ?
                    ');
                    $users->execute([$room_id, $_SESSION['user']['user_id']]);
                    $usersList = $users->fetchAll(PDO::FETCH_ASSOC);
                
                    // ユーザーがいるかどうか
                    if ($usersList) {
                        // 初期表示、全件表示
                        foreach($usersList as $user) {
                            // ユーザー情報を持ってくる
                            $members = $pdo->prepare('select * from Users where user_id=?');
                            $members->execute([$user['user_id']]);
                            $member = $members->fetch(PDO::FETCH_ASSOC);

                            // アイコン情報を持ってくる
                            $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                            $iconStmt->execute([$user['user_id']]);
                            $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                
                            echo '<li style="list-style: none; padding-left: 0;">
                                    <div class="profile-container"><div class="user-container">
                                    <img src="', htmlspecialchars($icon['icon_name']), '" width="20%" height="50%" class="usericon">
                                      <span><a href="user.php?user_id=' . htmlspecialchars($user['user_id']) . '">', htmlspecialchars($member['user_name']) .'</a></span>
                                </li>';
                        }
                    } else {
                        // 条件に合うユーザーが見つからなかった場合のメッセージ
                        echo '<p>ユーザーが見つかりませんでした。</p>';
                    }
                }

            }else if($_POST['target'] == "student"){
                // 初期分岐と未選択時
                if(empty($_POST['favorite']) || $_POST['favorite'] == 0) {
                    // 教室にいるメンバーを持ってくる(全件表示)
                    $users = $pdo->prepare('
                        SELECT Users.* FROM Users
                        JOIN Current_location ON Users.user_id = Current_location.user_id
                        WHERE Current_location.classroom_id = ? AND Users.s_or_t = 0
                    ');
                    $users->execute([$room_id]);
                    $usersList = $users->fetchAll(PDO::FETCH_ASSOC);
                
                    // ユーザーがいるかどうか
                    if ($usersList) {
                        // 初期表示、全件表示
                        foreach($usersList as $user) {
                            // ユーザー情報を持ってくる
                            $members = $pdo->prepare('select * from Users where user_id=?');
                            $members->execute([$user['user_id']]);
                            $member = $members->fetch(PDO::FETCH_ASSOC);

                            // アイコン情報を持ってくる
                            $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                            $iconStmt->execute([$user['user_id']]);
                            $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                
                            echo '<li style="list-style: none; padding-left: 0;">
                                    <div class="profile-container"><div class="user-container">
                                    <img src="'. htmlspecialchars($icon['icon_name']). '" width="20%" height="50%" class="usericon">
                                    <span><a href="user.php?user_id=' . htmlspecialchars($user['user_id']) . '">', htmlspecialchars($member['user_name']) .'</a></span>
                                  </li>';
                        }
                    } else {
                        // 条件に合うユーザーが見つからなかった場合のメッセージ
                        echo '<p>ユーザーが見つかりませんでした。</p>';
                    }
                

                // お気に入り選択時
            }else if($_POST['favorite'] == 1) {
                // 教室にいるメンバーを持ってくる(お気に入りに登録している場合)
                $users = $pdo->prepare('
                    SELECT Users.* FROM Users
                    JOIN Current_location ON Users.user_id = Current_location.user_id
                    JOIN Favorite ON Users.user_id = Favorite.follower_id
                    WHERE Current_location.classroom_id = ? AND Users.s_or_t = 0 AND Favorite.follow_id = ?
                ');
                $users->execute([$room_id, $_SESSION['user']['user_id']]);
                $usersList = $users->fetchAll(PDO::FETCH_ASSOC);
            
                // ユーザーがいるかどうか
                if ($usersList) {
                    // 初期表示、全件表示
                    foreach($usersList as $user) {
                        // ユーザー情報を持ってくる
                        $members = $pdo->prepare('select * from Users where user_id=?');
                        $members->execute([$user['user_id']]);
                        $member = $members->fetch(PDO::FETCH_ASSOC);
                    
                        // アイコン情報を持ってくる
                        $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                        $iconStmt->execute([$user['user_id']]);
                        $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
            
                        echo '<li style="list-style: none; padding-left: 0;">
                                <div class="profile-container"><div class="user-container">
                                <img src="', htmlspecialchars($icon['icon_name']), '" width="20%" height="50%" class="usericon">
                                <span><a href="user.php?user_id=' . htmlspecialchars($user['user_id']) . '">', htmlspecialchars($member['user_name']) ,'</a></span>
                              </li>';
                    }
                } else {
                    // 条件に合うユーザーが見つからなかった場合のメッセージ
                    echo '<p>ユーザーが見つかりませんでした。</p>';
                }
            }
        }
            echo '</ul>';
            echo '</div>';
        ?>
    <!-- メイン(マップ)に戻る -->
    
         <?php echo '<input type="hidden" name="floor" value="', $_SESSION['floor']['kai'], '">' ?>
        <!--<button type="submit" class="back-link">戻る</button> 
    </form> -->
    </main>
</body>

</html>