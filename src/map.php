<?php
require 'parts/auto-login.php';
require 'header.php';
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="mob_css/map-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/map.css" media="screen and (min-width: 1280px)">

<title>MAP</title>


<body>


    <?php

    echo '<div class="map">';
    echo '<h1 class="title">MAP</h1>';

    $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE user_id=?');
    $sql->execute([$_SESSION['user']['user_id']]);
    $results = $sql->fetchAll(PDO::FETCH_ASSOC);

    //プルダウン
    echo '<form action="map.php" method="post">';
    echo '<select name="list" class="list">';

    if (!empty($results)) {
        echo '<option value="0">全て</option>';
        foreach ($results as $tag_list) {

            $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
            $sql_tag->execute([$tag_list['tag_id']]);
            $row_tag = $sql_tag->fetch();
            echo "<option value='", $row_tag['tag_id'], "'>", $row_tag['tag_name'], "</option>";

        }
    } else {
        echo '<option value="">-</option>';
    }
    echo '<input type="submit" value="絞込">';
    echo '</select><br><br>';

    //  map
    echo '<table>';
    for ($i = 7; $i > 0; $i--) {
        echo '<tr>';
        
        echo '<td class="block">';
        echo '<div style="display:inline-flex">';

        // 位置取得 階のIDを取得
        $floorStmt = $pdo->prepare('select * 
                                  from Classroom                                    
                                  where classroom_floor=?');
        $floorStmt->execute([$i]);
        $floor = $floorStmt->fetchAll(PDO::FETCH_ASSOC);

        $j = 1;
        $judge = 0;
        foreach ($floor as $f) {
            $classroom_id = $f['classroom_id'];

            //アイコン情報を持ってくる
            $iconStmt = $pdo->prepare('SELECT Icon.*, Current_location.*, Icon.user_id as icon_user_id 
                           FROM Icon
                           LEFT JOIN Current_location ON Icon.user_id = Current_location.user_id
                           WHERE classroom_id = ?');
            $iconStmt->execute([$classroom_id]);
            $icon = $iconStmt->fetchAll(PDO::FETCH_ASSOC);

            // アイコン表示
            foreach ($icon as $ic) {
                $user_id = $ic['icon_user_id'];
                if ($j > 5) {
                    // 7以上は表示しない
                    echo '<form action="floor.php" method="post">';
                    echo '<input type="hidden" name="floor" value=', $i, '>';
                    echo '<input type="image" src="img/iconover.png" width="12%" height="95%" class="usericon" alt="over">';
                    echo '</form>';
                    $judge = 1;
                    break;
                }
                echo '<a href="user.php?user_id=' . $user_id . '">';
                $name_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                $name_sql->execute([$user_id]);
                $name_row = $name_sql->fetch();
                echo '<img src="', $ic['icon_name'], '" width="12%" height=95%" class="usericon" title="'.$name_row['user_name'].'">';
                $j++;
                echo '</a>';
                
            }
            if ($judge == 1) {
                break;
            }
        }

        echo '</td>';
        echo '</div>';
        echo '<form name="floor" action="floor.php" method="post">';
        echo '<input type="hidden" name="floor" value=', $i, '>';
        echo '<td class="number"><button type="submit" class="floor" value="', $i, '" name="floor">', $i, '階</button></td>'; // 修正: buttonタグを閉じる位置
        echo '</tr>';
        echo '</form>';
    }
    echo '</table>';

    ?>

    </div>

    <div class="gakugai-container">
        <h2>学外</h2>
    </div>
    <br>
    <br>
</body>

</html>