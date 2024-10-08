<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Hamburger Menu</title>
</head>
<body>
  <header>
    <div class="header-area">
      <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </header>

  <!-- 背景オーバーレイ -->
  <div class="background-overlay"></div>

  <!-- スライドメニュー -->
  <div class="slide-menu">
    <ul>
      <li>MAP</li>
      <li>ユーザー情報</li>
      <li>お気に入り</li>
      <li>QRカメラ</li>
      <li>チャット</li>
      <li>みんなのタグ</li>
      <li>MYタグ</li>
      <li>アナウンス</li>
      <li class="logout">ログアウト</li>
    </ul>
  </div>

  <script>
    const hamburger = document.querySelector('.hamburger');
    const slideMenu = document.querySelector('.slide-menu');
    const overlay = document.querySelector('.background-overlay');

    hamburger.addEventListener('click', function() {
      this.classList.toggle('active');
      slideMenu.classList.toggle('active');
      overlay.classList.toggle('active');
    });
  </script>
</body>
</html>>