<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
</head>
<body>
  <header>
    <div class="header-area">
      <h1>ロゴ</h1>
      <div class="hamburger">
        <!-- ハンバーガーメニューの線 -->
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
      <li>メニュー</li>
      <li>メニュー2</li>
      <li>メニュー3</li>
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
      overlay.classList.toggle('active'); // 背景オーバーレイの表示・非表示を切り替える
    });
  </script>
</body>
</html>