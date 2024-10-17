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
    <div id="qrcode"><button type="button">QR表示</button></div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script>
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            width: 128,
            height: 128
        });

        function makeCode () {
            var elText = document.getElementById("text");

            if (!elText.value) {
                alert("Input a text");
                elText.focus();
                return;
            }

            qrcode.makeCode(elText.value);
        }

        makeCode();

        document.getElementById("text").addEventListener("input", makeCode);
    </script>
</body>
</html>
