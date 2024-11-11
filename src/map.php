<?php
require 'parts/auto-login.php';
require 'header.php';
unset($_SESSION['floor']['kai']);
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="mob_css/map-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/map.css" media="screen and (min-width: 1280px)">

<title>MAP</title>


<body>


    <?php

    echo '<div class="map">';
    echo '<h1 class="title">麻生情報ビジネス専門学校</h1>';

    $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE user_id=?');
    $sql->execute([$_SESSION['user']['user_id']]);
    $results = $sql->fetchAll(PDO::FETCH_ASSOC);

    //プルダウン
    echo '<form action="map.php" method="post">';
    $selected_tag = $_POST['favorite'] ?? 'no';
    echo '<select name="favorite" class="list">';
    echo '<option value="">ユーザー</option>';
    echo '<option value="yes"', ($selected_tag === 'yes' ? ' selected' : ''), '>登録済み</option>';
    echo '<option value="no"', ($selected_tag === 'no' ? ' selected' : ''), '>全ユーザー</option>';
    echo '</select>';
    echo '<select name="tag_list" class="list">';
    echo '<option value="">タグ</option>';

    // POSTデータから選択されたタグの値を取得
    $selected_tag = $_POST['tag_list'] ?? '0'; // デフォルトで「全て」を選択
    
    if (!empty($results)) {
        echo '<option value="0"', ($selected_tag === '0' ? ' selected' : ''), '>全て</option>';

        // Tag_listテーブルからすべてのタグを一度に取得
        $tag_ids = array_column($results, 'tag_id');
        $placeholders = implode(',', array_fill(0, count($tag_ids), '?'));
        $sql_tag = $pdo->prepare("SELECT * FROM Tag_list WHERE tag_id IN ($placeholders)");
        $sql_tag->execute($tag_ids);

        // 各タグをリストに表示
        foreach ($sql_tag as $row_tag) {
            $tag_id = $row_tag['tag_id'];
            $tag_name = htmlspecialchars($row_tag['tag_name'], ENT_QUOTES, 'UTF-8');
            $selected = ($tag_id == $selected_tag) ? ' selected' : '';
            echo "<option value='{$tag_id}'{$selected}>{$tag_name}</option>";
        }
    } else {
        echo '<option value=0>-</option>';
    }
    echo '<input type="submit" class="abst" value="絞込">';
    echo '</select><br><br>';
    echo '</form>';


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
                if (isset($p_tag_id)) {
                    unset($p_tag_id);
                }
                if (isset($_POST['favorite'])) {
                    if ($_POST['favorite'] == "yes") {
                        $favorite_sql = $pdo->prepare('SELECT * FROM Favorite WHERE follow_id=? AND follower_id=?');
                        $favorite_sql->execute([$_SESSION['user']['user_id'], $user_id]);
                        $favorite_row = $favorite_sql->fetch();
                        if (!($favorite_row)) {
                            break;
                        }
                        if (isset($_POST['tag_list'])) {
                            if ($_POST['tag_list'] != 0) {
                                $p_tag_id = intval($_POST['tag_list']);
                                $tag_sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
                                $tag_sql->execute([$p_tag_id, $user_id]);
                                $tag_row = $tag_sql->fetch();
                                if (!($tag_row)) {
                                    break;
                                }
                            }
                        }
                    } else {
                        if (isset($_POST['tag_list'])) {
                            if ($_POST['tag_list'] != 0) {
                                $p_tag_id = intval($_POST['tag_list']);
                                $tag_sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
                                $tag_sql->execute([$p_tag_id, $user_id]);
                                $tag_row = $tag_sql->fetch();
                                if (!($tag_row)) {
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($j > 7) {
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
                echo '<img src="', $ic['icon_name'], '" width="12%" height=95%" class="usericon" title="' . $name_row['user_name'] . '">';
                $j++;
                echo '</a>';

            }
            if ($judge == 1) {
                break;
            }

        }
        if ($j == 1) {
            echo '<span>ユーザーがいません</span>';
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

        <h2 class="gakugai-container"><a href="mapindex.php">学外</a></h2>
    <br>
    <br>
</body>

</html>