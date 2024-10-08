<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/test.css">
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
        <!-- /ハンバーガーメニューの線 -->
      </div>
    </div>
    <ul class="slide-menu">
      <li>メニュー</li>
      <li>メニュー2</li>
      <li>メニュー3</li>
      <li>メニュー4</li>
    </ul>
  </header>
  <script>
    document.querySelector('.hamburger').addEventListener('click', function(){
      this.classList.toggle('active');
      document.querySelector('.slide-menu').classList.toggle('active');
    })
  </script>
</body>
</html>