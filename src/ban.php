<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

    <title>アカウント停止</title>
    <style>
        /* 画面全体に画像を表示するためのスタイル */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #000;
        }

        .fullscreen-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* 画像を全体に拡大して表示 */
        }

        #soredemo {
            display: none; /* 初期状態で非表示 */
        }
    </style>
</head>
<body>

    <!-- ban.png を画面全体に表示 -->
    <img id="ban-image" src="image/ban.png" alt="アカウント停止" class="fullscreen-image">

    <!-- soredemo.gif を3秒後に表示 -->
    <img id="soredemo" src="image/soredemo.gif" alt="それでも" class="fullscreen-image">

    <script>
        // 3秒後に ban.png を非表示にし、soredemo.gif を表示する
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('ban-image').style.display = 'none'; // ban.png を非表示
                document.getElementById('soredemo').style.display = 'block'; // soredemo.gif を表示
            }, 3000); // 3000ミリ秒 = 3秒
        });
    </script>

</body>
</html>
