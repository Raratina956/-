<?php 
    require 'parts/auto-login.php';
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="mob_css/map-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/map.css" media="screen and (min-width: 1280px)">

<?php require 'header.php'; 
?>

    <title></title>
   
   
    <body>

        <div class="map">
            <h1>MAP</h1>
          
        <?php

        //  $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE user_id=?');
        //  $sql->execute([$_SESSION['user']['user_id']]);
        //  $results = $sql->fetchAll(PDO::FETCH_ASSOC);

        //  //プルダウン
        //  echo '<select name="list" class="list">';
        //  foreach ($results as $tag_list) {

        //     $sql_tag = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
        //     $sql_tag->execute([$tag_list['tag_id']]);
        //     $row_tag = $sql_tag->fetch();
        //     echo "<option value='",$row_tag['tag_id'],"'>",$row_tag['tag_name'],"</option>"; 

        //   }
        //  echo '</select><br><br>';

        //  map
        echo '<table width=700>';
           for($i = 7;$i>0; $i--){
                echo '<tr>';
                echo '<form name="floor" action="floor.php" method="post">';
                echo '<td class="block">';
                // 位置情報アイコン
                // 位置取得 階のIDを取得
                $floorStmt=$pdo->prepare('select * 
                                         from Classroom                                    
                                         where classroom_floor=?');
                $floorStmt->execute([$i]);
                $floor = $floorStmt->fetchAll(PDO::FETCH_ASSOC);
                $class_id = "";

                    foreach($floor as $f){
                        $classroom_id = $f['classroom_id'];
                    
                        //アイコン情報を持ってくる
                        $iconStmt=$pdo->prepare('select * 
                                                from Icon                                         
                                                LEFT JOIN Current_location On Icon.user_id = Current_location.user_id
                                                where classroom_id=?');
                        $iconStmt->execute([$classroom_id]);
                        $icon = $iconStmt->fetchAll(PDO::FETCH_ASSOC);
                        // アイコン表示
                            foreach($icon as $ic){
                                echo '<img src="', $ic['icon_name'], '" width="12%" height=95%" class="usericon">';
                            }
                    }

                echo '</td>';
                echo '<input type="hidden" name="floor" value=', $i, '>';
                echo '<td class="number"><button type="submit" value="',$i,'" name="floor">',$i,'階</td>';
                echo '</tr>';
                echo '</form>';
            }
        echo '<table>';

        ?>

        </div>
  
        <div class="gakugai-container">
        <h2>学外</h2>
        </div>
        <br>
        <br>
    </body>
</html>