<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/qr_show.css">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

    <title>QR表示</title>
</head>
<body>
<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_url'])) {
        $customUrl = htmlspecialchars($_POST['custom_url'], ENT_NOQUOTES);
        echo '<img src="img/icon.png"><br>';
        echo '<div class="room">', $_POST['room'], '教室</div>';
        echo '<div id="qrcode-container"><div id="qrcode"></div></div>';
        // echo '送信されたURL: ' . $customUrl;
    ?>
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
        <script>
            // QRコードを生成
            new QRCode(document.getElementById("qrcode"), {
                text: "<?php echo $customUrl; ?>",
                width: 256,
                height: 256
            });
        </script>
    <?php
    } else {
        echo 'URLが送信されていません。';
    }
?>

</body>
</html>
