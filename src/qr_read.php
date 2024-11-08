<?php
    require 'parts/auto-login.php';
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRコード読み取り</title>
    <link rel="stylesheet" href="css/qr_read.css" media="screen and (min-width: 1280px)">
    <link rel="stylesheet" href="mob_css/qr_read.css" media="screen and (max-width: 480px)">
</head>

<body>
    <div id="wrapper">
        <video id="video" autoplay muted playsinline></video>
        <canvas id="camera-canvas"></canvas>
        <canvas id="rect-canvas"></canvas>
        <span id="qr-msg">QRコード: 見つかりません</span>
    </div>
    <script src="js/jsQR.js"></script>
    <script src="js/qr_read.js"></script>
</body>

</html>