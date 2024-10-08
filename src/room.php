<?php
require 'parts/auto-login.php';
?>

<?php
require 'header.php';
?>
<?php
if(isset($_GET['id'])){
    echo $_GET['id'];
}else{
    echo 'なし';
}
?>