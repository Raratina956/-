<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/qr.css">
    <script src="js/qr_show.js"></script>
    <title>QR表示</title>
</head>
<body>
    <input id="text" type="text" value="https://hogangnono.com" style="width:80%" /><br />
    <div id="qrcode"></div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script>
        const textInput = document.getElementById('text');
        const qrCodeDiv = document.getElementById('qrcode');

        const generateQRCode = () => {
            qrCodeDiv.innerHTML = ''; // 既存のQRコードをクリア
            QRCode.toCanvas(qrCodeDiv, textInput.value, function (error) {
                if (error) console.error(error);
                console.log('QRコード生成完了!');
            });
        };

        // 初期ロード時にQRコードを生成
        generateQRCode();

        // テキスト入力が変わるたびにQRコードを生成
        textInput.addEventListener('input', generateQRCode);
    </script>
</body>
</html>
