<?php
require 'parts/auto-login.php';
require 'header.php';
unset($_SESSION['floor']['kai']);
?>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="mob_css/map-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/map.css" media="screen and (min-width: 481px) and (max-width: 1279px)">
<link rel="stylesheet" href="css/map.css" media="screen and (min-width: 1280px)">
<!-- font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

<title>MAP</title>
<style>
    .icon-modal {
        display: none;
        /* 初期状態では非表示 */
        position: fixed;
        /* 固定位置 */
        top: 0;
        left: 10px;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        /* 少し透明な白に設定 */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1001;
        /* モーダルが最前面に来るようにZインデックスを調整 */
    }

    .icon-modal img,
    .icon-modal video {
        width: 700px;
        /* アイコンのサイズを調整 */
        opacity: 1;
        /* 初期透明度 */
        transition: opacity 1s ease-out;
        /* フェードアウトのアニメーション */
        clip-path: inset(0 50px 0 0);
        /* 右端を50pxカット */
    }

    /* Add media query for mobile devices */
    @media screen and (max-width: 480px) {

        .icon-modal img,
        .icon-modal video {
            width: 300px;
            /* Smaller size for mobile */
        }
    }

    .usericon {
        transition: transform 0.3s ease-in-out;
    }
    .usericon:hover {
        transform: scale(1.1);
    }
</style>

<body>


    <?php
    if (!isset($_COOKIE['img_displayed'])) {
        echo '<div class="icon-modal" id="icon-modal">
                <video id="icon" autoplay loop muted>
                    <source src="img/icon_move.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>';
        setcookie('img_displayed', 'true', time() + (86400 * 30), "/");
        // Cookie valid for 30 days
    }



    echo '<div class="map">';
    echo '<h1 class="title">麻生情報ビジネス専門学校</h1>';

    $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE user_id=?');
    $sql->execute([$_SESSION['user']['user_id']]);
    $results = $sql->fetchAll(PDO::FETCH_ASSOC);

    //プルダウン
    echo '<div class="select">';
    echo '<form action="map.php" method="post" class="user-kensaku">';
    $selected_tag = $_POST['favorite'] ?? 'no'; ?>

    <p class="kensaku-user">ユーザー絞り込み</p>
    <div class="container">
        <div class="text">◯お気に入りユーザー<input type="checkbox" name="favorite" class="list" value="yes" <?php echo ($_POST['favorite'] ?? 'no') === 'yes' ? 'checked' : ''; ?>></div>
        <?php
        // echo 'ユーザー絞り込み<br>お気に入り<select name="favorite" class="list">';
        // // echo '<option value="">ユーザー</option>';
        // echo '<option value="yes"', ($selected_tag === 'yes' ? ' selected' : ''), '>お気に入り登録済み</option>';
        // echo '<option value="no"', ($selected_tag === 'no' ? ' selected' : ''), '>全ユーザー</option>';
        // echo '</select>';
        echo '<div class="text">◯タグ<label class="selectbox-5"><select name="tag_list" class="list">';
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
        echo '</select></label></div>';
        echo '</div>';
        echo '<input type="submit" class="abst" value="絞込">';
        echo '</form></div>';

        // 学外
        echo '<div class="gakugai-container"><li><a  href="mapindex.php"><img src="img/gakugai.png" width="150px" height="150px"></a></li><li><a style="position: absolute;top: 117px;left: 30px;">学外はこちら</a></li></div>';

        //  map
        echo '<br><table class="table">';
        for ($i = 7; $i > 0; $i--) {
            echo '<tr>';

            echo '<td class="block">';
            echo '<div style="display:inline-flex">';

            // 位置取得 階のIDを取得階
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
                    // if (isset($p_tag_id)) {
                    //     unset($p_tag_id);
                    // }
                    if (isset($_POST['favorite']) && !empty($_POST['favorite'])) {
                        if ($_POST['favorite'] == "yes") {
                            $favorite_sql = $pdo->prepare('SELECT * FROM Favorite WHERE follow_id=? AND follower_id=?');
                            $favorite_sql->execute([$_SESSION['user']['user_id'], $user_id]);
                            $favorite_row = $favorite_sql->fetch();
                            if (!($favorite_row)) {
                                continue;
                            }
                            if (isset($_POST['tag_list'])) {
                                if ($_POST['tag_list'] != 0) {
                                    $p_tag_id = intval($_POST['tag_list']);
                                    $tag_sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
                                    $tag_sql->execute([$p_tag_id, $user_id]);
                                    $tag_row = $tag_sql->fetch();
                                    if (!($tag_row)) {
                                        continue;
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
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                    if (isset($_POST['tag_list'])) {
                        if ($_POST['tag_list'] != 0) {
                            $p_tag_id = intval($_POST['tag_list']);
                            $tag_sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
                            $tag_sql->execute([$p_tag_id, $user_id]);
                            $tag_row = $tag_sql->fetch();
                            if (!($tag_row)) {
                                continue;
                            }
                        }
                    }

                    if ($j > 6) {
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
                echo '<span class="not_user">ユーザーがいません</span>';
            }

            echo '</td>';
            echo '</div>';
            echo '<form name="floor" action="floor.php" method="post" onsubmit="return handleFormSubmit();">';
            echo '<input type="hidden" name="floor" value=', $i, '>';
            // $_SESSION['floor']['kai'] = $i;
            echo '<td class="number"><button type="submit" class="floor" value="', $i, '" name="floor">', $i, '階</button></td>'; // 修正: buttonタグを閉じる位置
            // echo '<td class="number">test</td>'; // 修正: buttonタグを閉じる位置
            echo '</tr>';
            echo '</form>';
        }
        echo '</table>';
        ?>

    </div>
    <br>
    <script>
        window.onload = function () {
            const iconModal = document.getElementById('icon-modal');
            const icon = document.getElementById('icon');
            iconModal.style.display = 'flex';// モーダルを表示
            setTimeout(function () {
                icon.style.opacity = '0'; // フェードアウト開始
            }, 1500); // 1秒間表示してからフェードアウトを開始
            setTimeout(function () {
                iconModal.style.display = 'none'; // フェードアウト後にモーダルを非表示
            }, 2000); // フェードアウトが完了するまで待つ
        };

        document.addEventListener("DOMContentLoaded", function () {
            if (document.querySelector('#icon-modal')) {
                // Show the modal document.querySelector('#icon-modal').style.display = 'block';
            }
        });
        
        function handleFormSubmit() {
            // ここで特定の処理を行う（例: ログ出力やバリデーションなど）
            // unset($_SESSION['floor']['kai']);

            // 必要な処理が完了したらフォーム送信を許可する
            return true; // フォームの送信を続行
        }
    </script>
</body>

</html>