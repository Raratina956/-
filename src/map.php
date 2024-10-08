<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/map.css">
    <title>Document</title>
   
</head>
<?php require 'header.php'; ?>
    <body>
        <div class="map">
            <h2>MAP</h2>
        <?php
    
            for($i = 7;$i>0; $i--){
            
                echo '<div class="number">',$i,'</div>';
                echo '<div class="block">ここに文字を入力する。</div>';
            

            }
    

        ?>

        </div>
    
    </body>
</html>