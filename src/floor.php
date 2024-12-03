<?php
require 'parts/auto-login.php';
if (!isset($_SESSION['floor']['kai'])) {
    $_SESSION['floor'] = [
        'kai' => $_POST['floor']
    ];
}
require 'header.php';
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($_SESSION['floor']['kai']); ?>階</title>
    <link rel="stylesheet" href="mob_css/floor-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/floor.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
    <link rel="stylesheet" href="css/floor.css" media="screen and (min-width: 1280px)">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

</head>

<body>
    <!-- メイン(マップ)に戻る -->
    <button type="button" class="back-link" onclick="location.href='map.php'">戻る</button>
    <main>
        <?php
        echo '<h1>', htmlspecialchars($_SESSION['floor']['kai']), '階</h1>';
        if (!$isMobile) {
            echo '<form action="floor.php" method="post">';
            echo '<input type="hidden" name="floor" value=', $_SESSION['floor']['kai'], '>';
            echo '<h3>お気に入りユーザー</h3><input type="checkbox" name="favorite" class="check" value="yes" ' . (($_POST['favorite'] ?? 'no') === 'yes' ? 'checked' : '') . '>';
            echo '<input type="submit" value="絞込"class="sort">';
        }
        $sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_floor = ?');
        $sql->execute([$_SESSION['floor']['kai']]);
        $rows = $sql->fetchAll(PDO::FETCH_ASSOC);

        $sql_current = $pdo->prepare('SELECT classroom_id, COUNT(*) AS user_count FROM Current_location GROUP BY classroom_id');
        $sql_current->execute();
        $results = $sql_current->fetchAll(PDO::FETCH_ASSOC);

        $ul_class = count($rows) < 5 ? 'vertical' : 'horizontal';

        echo '<ul class="ul1 ', $ul_class, '">';
        foreach ($rows as $row) {
            $classroom_id = $row['classroom_id'];
            $classroom_name = $row['classroom_name'];
            $user_count = 0;

            foreach ($results as $result) {
                if ($result['classroom_id'] == $classroom_id) {
                    $user_count = $result['user_count'];
                    break;
                }
            }

            echo '<li class="li1">';
            echo '<a class="a1" href="room.php?id=', htmlspecialchars($classroom_id), '&update=0">', '<span class="san">‣</span>', htmlspecialchars($classroom_name), '　', $user_count, '人</a>'; // htmlspecialcharsでXSS対策
            if (!$isMobile) {
                $current_sql = $pdo->prepare('SELECT * FROM Current_location WHERE classroom_id=?');
                $current_sql->execute([$classroom_id]);
                $current_row = $current_sql->fetchAll();
                $favorite_sql = $pdo->prepare('SELECT * FROM Favorite WHERE follow_id=?');
                $favorite_sql->execute([$_SESSION['user']['user_id']]);
                $favorite_row = $favorite_sql->fetchAll();
                if ($current_row) {
                    $icon_count = 0;
                    foreach ($current_row as $current_list) {
                        $icon_user_id = $current_list['user_id'];
                        $iconStmt = $pdo->prepare('select icon_name from Icon where user_id=?');
                        $iconStmt->execute([$icon_user_id]);
                        $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                        if ($icon_count < 3) {
                            if (isset($_POST['favorite']) && !empty($_POST['favorite'])) {
                                if ($_POST['favorite'] == "yes") {
                                    if ($favorite_row) {
                                        foreach ($favorite_row as $favorite_list) {
                                            $follower_id = $favorite_list['follower_id'];
                                            if ($icon_user_id == $follower_id) {
                                                echo '<a href="user.php?user_id=' . $icon_user_id . '">';
                                                echo '<img src="', $icon['icon_name'], '" width="12%" height="95%" class="usericon">';
                                                echo '</a>';
                                                $icon_count++;
                                            }
                                        }
                                    }
                                    continue;
                                }
                            }
                            echo '<a href="user.php?user_id=' . $icon_user_id . '">';
                            echo '<img src="', $icon['icon_name'], '" width="12%" height="95%" class="usericon">';
                            echo '</a>';
                            $icon_count++;
                        } else {
                            break;
                        }
                    }
                }
            }
            echo '</li>';
        }
        echo '</ul>';
        ?>
    </main>
</body>


</html>