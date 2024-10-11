<?php 
    require 'parts/auto-login.php';
    require 'header.php'; 
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/map.css">
    <title>Document</title>
   
</head>
   
    <body>

    <?php
    // echo 'ユーザーID：', $_SESSION['user']['user_id'];
    // echo 'ユーザー名：', $_SESSION['user']['user_name'];
    // $sql_room = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
    // $sql_room->execute([$_SESSION['user']['user_id']]);
    // $row_room = $sql_room->fetch();
    // if (!$row_room) {
    //     $current_name = '未登録';
    // } else {
    //     $current_id = $row_room['classroom_id'];
    //     $sql_room = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_id=?');
    //     $sql_room->execute([$current_id]);
    //     $row_room = $sql_room->fetch();
    //     $current_name = $row_room['classroom_name'];
    // }
    // echo '現在位置：', $current_name;
    ?> 
        <div class="map">
            <h1>MAP</h1>
          
        <?php
         $sql = $pdo->prepare('SELECT * FROM Tag_list WHERE user_id=?');
         $sql->execute([$_SESSION['user']['user_id']]);
         $row = $sql->fetch(PDO::FETCH_ASSOC);

         foreach ($sql as $tag_list) {
               echo '<select name="list">';
               echo "<option value='",$tag_list['tag_id'],"'>'",$tag_list['tag_name'],"'</option>";
               echo '</select>';

            // $data .= "<option value='". $tag_list['tag_name'];
            // $data .= "'>". $tag_list['tag_name']. "</option>";
          }
         
            echo '<table width=700>';
                for($i = 7;$i>0; $i--){
                    echo '<tr>';
                    echo '<form name="floor" action="floor.php" method="post">';
                    echo '<td class="block"><div class="box">aaaaa</div></td>';
                    echo '<input type="hidden" name="floor" value=', $i, '>';
                    echo '<td class="number"><button type="submit" value="',$i,'階" name="floor"">',$i,'階</td>';
                    echo '</tr>';
                    echo '</form>';
                }
            echo '<table>';

        ?>

        </div>
        <br>
        <hr>
        <br>
        <div class="gakugai-container">
        <h2>学外</h2>
        </div>
        <li><a href="my_tag.php">タグ作成</a></li>
        <li><a href="tag_list.php">タグ一覧</a></li>
        <li><a href="map.php">map</a></li>
     
    </body>
</html>