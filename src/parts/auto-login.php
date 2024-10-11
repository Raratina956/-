<?php
ob_start();
session_start();
require 'parts/db-connect.php';
if (isset($_SESSION['login'])) {
    unset($_SESSION['login']);
}
if (!(isset($_SESSION['user']))) {
    $_SESSION['login']['error'] = 'ログインをしてください';
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/login.php';
    header("Location: $redirect_url");
    exit();
}
?>