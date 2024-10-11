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

        <div class="map">
            <h1>MAP</h1>
          
        <?php
         $sql = $pdo->prepare('SELECT * FROM Tag_list WHERE user_id=?');
         $sql->execute([$_SESSION['user']['user_id']]);
         $row = $sql->fetchAll(PDO::FETCH_ASSOC);

         //プルダウン
         echo '<select name="list">';
         foreach ($row as $tag_list) {
            echo "<option value='",$tag_list['tag_id'],"'>",$tag_list['tag_name'],"</option>"; 

          }
         echo '</select>';
         
        //  map
            echo '<table width=700>';
                for($i = 7;$i>0; $i--){
                    echo '<tr>';
                    echo '<form name="floor" action="floor.php" method="post">';
                    echo '<td class="block"><div class="box">aaaaa</div></td>';
                    echo '<input type="hidden" name="floor" value=', $i, '>';
                    echo '<td class="number"><button type="submit" value="',$i,'階" name="floor">',$i,'階</td>';
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
    </body>
</html>