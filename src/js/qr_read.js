// Webカメラの起動
const video = document.getElementById('video');
let contentWidth;
let contentHeight;
let lastScannedUrl = ''; // 最後にスキャンされたURLを追跡
let stream; // グローバルにストリームを保持

// 背面カメラを指定するためのconstraints
const constraints = {
    audio: false,
    video: {
        facingMode: { ideal: "environment" }, // 背面カメラを指定
        width: { ideal: 640 }, // 幅
        height: { ideal: 480 } // 高さ
    }
};

function startCamera() {
    navigator.mediaDevices.getUserMedia(constraints)
        .then((mediaStream) => {
            stream = mediaStream; // ストリームを保持
            video.srcObject = stream;
            video.onloadeddata = () => {
                video.play();
                contentWidth = video.clientWidth;
                contentHeight = video.clientHeight;
                canvasUpdate();
                checkImage();
            }
        }).catch((e) => {
            console.log(e);
        });
}

startCamera(); // 初回カメラ起動

// カメラ映像のキャンバス表示
const cvs = document.getElementById('camera-canvas');
const ctx = cvs.getContext('2d');

const canvasUpdate = () => {
    cvs.width = contentWidth;
    cvs.height = contentHeight;
    ctx.drawImage(video, 0, 0, contentWidth, contentHeight);
    requestAnimationFrame(canvasUpdate);
}

// QRコードの検出
const rectCvs = document.getElementById('rect-canvas');
const rectCtx = rectCvs.getContext('2d');

const checkImage = () => {
    const imageData = ctx.getImageData(0, 0, contentWidth, contentHeight);
    const code = jsQR(imageData.data, contentWidth, contentHeight);

    if (code) {
        console.log("QRコードが見つかりました", code);
        drawRect(code.location);
        const qrCodeUrl = code.data;
        const qrMsg = document.getElementById('qr-msg');

        // URLを条件に応じて表示
        if (qrCodeUrl.startsWith("https://aso2201203.babyblue.jp/Nomodon/src")) {
            qrMsg.innerHTML = `QRコード：<a href="${qrCodeUrl}" target="_blank">${qrCodeUrl}</a>`;
        } else {
            qrMsg.textContent = "外部のQRコードです";
        }

        // URLを一度だけ新規タブで開く
        if (qrCodeUrl !== lastScannedUrl) {
            if (qrCodeUrl.startsWith("https://aso2201203.babyblue.jp/Nomodon/src")) {
                window.open(qrCodeUrl, '_blank');
            } else {
                alert("外部のQRコードです");
                stopCamera();
                window.location.reload(); // ページをリロード
            }
            lastScannedUrl = qrCodeUrl; // 最後にスキャンされたURLを更新
        }
    } else {
        console.log("QRコードが見つかりません…", code);
        rectCtx.clearRect(0, 0, contentWidth, contentHeight);
        document.getElementById('qr-msg').textContent = `QRコード: 見つかりません`;
    }

    if (!stream.getTracks().every(track => track.readyState === 'ended')) {
        setTimeout(() => { checkImage() }, 500);
    }
};


// カメラの停止
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
}

// 四辺形の描画
const drawRect = (location) => {
    rectCvs.width = contentWidth;
    rectCvs.height = contentHeight;
    drawLine(location.topLeftCorner, location.topRightCorner);
    drawLine(location.topRightCorner, location.bottomRightCorner);
    drawLine(location.bottomRightCorner, location.bottomLeftCorner);
    drawLine(location.bottomLeftCorner, location.topLeftCorner);
}

// 線の描画
const drawLine = (begin, end) => {
    rectCtx.lineWidth = 4;
    rectCtx.strokeStyle = "#F00";
    rectCtx.beginPath();
    rectCtx.moveTo(begin.x, begin.y);
    rectCtx.lineTo(end.x, end.y);
    rectCtx.stroke();
}
