<?php
session_start();
require 'parts/db-connect.php';

if (!(isset($_SESSION['user']))) {
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/login.php';
    header("Location: $redirect_url");
    exit();
}
?>