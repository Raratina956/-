<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/qr_show.css">
    <title>QR表示</title>
</head>
<body>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['custom_url'])) {
        $customUrl = htmlspecialchars($_POST['custom_url']);
        echo '<input id="text" type="text" value="' . $customUrl . '" style="width:80%" /><br />';
        echo '<div id="qrcode"></div>';
        echo '送信されたURL: ' . $customUrl;
        // QRコード生成ロジックをここに追加する
    ?>
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
        <script>
            var qrcode = new QRCode(document.getElementById("qrcode"), {
                width: 128,
                height: 128
            });

            function makeCode() {
                var elText = document.getElementById("text");

                if (!elText.value) {
                    elText.focus();
                    return;
                }

                // 既存のQRコードをクリア
                document.getElementById("qrcode").innerHTML = "";

                // 新しいQRコードを生成
                qrcode.makeCode(elText.value);
            }

            makeCode();

            document.getElementById("text").addEventListener("input", makeCode);
        </script>
    <?php
    } else {
        echo 'URLが送信されていません。';
    }
    ?>
</body>
</html>
