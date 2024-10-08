<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/sign-up-input.css">
    <title>ログイン</title>
</head>

<body>
    <a href="login.php">戻る</a>
    <h2>新規会員登録</h2>
    <form action="Sign-up-output.php" method="post">
        メールアドレス：<input type="text" name="mail_address" required>
        <br>
        分類：<select name="type">
           		<option value="0">学生</option>
           		<option value="1">教師</option>
           	</select>
        <br>
        名前：<input type="text" name="name" required>
        <br>
        パスワード：<input type="text" name="password" required>
        <br>
        パスワード(確認用)：<input type="text" name="confirm_password" required> <!-- フィールド名を変更 -->
        <br>
        <input type="submit" value="登録">
    </form>
</body>

</html>






