<?php
require 'parts/auto-login.php';
?>
ログイン後のページ
<p>
<?php
echo 'ユーザーID：',$_SESSION['user']['user_id'];
echo 'ユーザー名：',$_SESSION['user']['user_name'];
?>
</p>
<ul>
    <?php
    for($i=1;$i<=6;$i++){
    echo '<li>';
        echo '<form action="floor.php" method="post">';
            echo '<input type="hidden" name="floor" value="',$i,'">';
            echo '<input type="submit" value="',$i,'階">';
        echo '</form>';
    echo '</li>';
    }
    ?>
</ul>
