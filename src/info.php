<?php
require 'parts/auto-login.php';
// Announce_check参照
$list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
$list_sql->execute([$_SESSION['user']['user_id']]);
$list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
if ($list_raw) {
    echo '<table>';
    foreach ($list_raw as $row) {
        switch ($row['type']) {
            case 1:
                echo 'お知らせ';
                break;
            
            case 2:
                echo '位置情報';
                    break;
            default:
                echo 'その他';
                break;
        }
    }
    echo '</table>';
}else{
    echo 'お知らせがありません';
}
?>





<?php
require 'header.php';
?>
<link rel="stylesheet" href="css/info.css">
<h1>お知らせ</h1>