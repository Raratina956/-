<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/qr_show.css">
    <title>QR表示</title>
</head>
<body>
    <input id="text" type="text" value="https://hogangnono.com" style="width:80%" /><br />
    <div id="qrcode"></div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            width: 128,
            height: 128
        });

        function makeCode() {
            var elText = document.getElementById("text");

            if (!elText.value) {
                alert("Input a text");
                elText.focus();
                return;
            }

            // 既存のQRコードをクリア
            document.getElementById("qrcode").innerHTML = "";

            // 新しいQRコードを生成
            qrcode = new QRCode(document.getElementById("qrcode"), {
                text: elText.value,
                width: 128,
                height: 128
            });
        }

        makeCode();

        document.getElementById("text").addEventListener("input", makeCode);
    </script>
</body>
</html>
