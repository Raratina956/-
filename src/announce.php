<?php
require 'parts/auto-login.php';

?>
<?php
// require 'header.php';
?>
<h1>アナウンス</h1>
<form action="announce.php" method="post">
    <select name="tag">
        <option>選択肢のサンプル1</option>
        <option>選択肢のサンプル2</option>
        <option>選択肢のサンプル3</option>
    </select>
    <input type="textarea" name="content">
    <input type="submit" value="送信">
</form>