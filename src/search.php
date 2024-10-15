<?php
require 'parts/auto-login.php';
$search_text = $_POST['search'];
?>

<?php
// require 'header.php';
?>
<h1>検索結果</h1>
<h2><?php $search_text ?></h2>

<?php
$judge = 0;
$pe_user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_name=?');
$pe_user_sql->execute([$search_text]);
$pe_user_raw = $pe_user_sql->fetchAll(PDO::FETCH_ASSOC);
if ($pe_user_raw) {
    $judge = 1;
}
$pe_tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_name=?');
$pe_tag_sql->execute([$search_text]);
$pe_tag_raw = $pe_tag_sql->fetchAll(PDO::FETCH_ASSOC);
if ($pe_tag_raw) {
    $judge = 1;
}
$se_user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_name LIKE ?');
$se_user_sql->execute(['%' . $search_text . '%']);
$se_user_raw = $se_user_sql->fetchAll(PDO::FETCH_ASSOC);
if ($se_user_raw) {
    $judge = 1;
}
$se_tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_name LIKE ?');
$se_tag_sql->execute(['%' . $search_text . '%']);
$se_tag_raw = $se_tag_sql->fetchAll(PDO::FETCH_ASSOC);
if ($se_user_raw) {
    $judge = 1;
}

if ($judge == 0) {
    echo '<table>';
    if ($pe_user_raw) {
        foreach ($pe_user_raw as $row) {
            ?>
            <tr>
                <td>アイコン</td>
                <td><?php $row['user_name']; ?></td>
            </tr>
            <?php
        }
    }
    if ($pe_tag_raw) {
        foreach ($pe_tag_raw as $row) {
            ?>
            <tr>
                <td>タグ</td>
                <td><?php $row['tag_name']; ?></td>
            </tr>
            <?php
        }
    }
    if ($se_user_raw) {
        foreach ($se_user_raw as $row) {
            ?>
            <tr>
                <td>アイコン</td>
                <td><?php $row['user_name']; ?></td>
            </tr>
            <?php
        }
    }
    if ($se_tag_raw) {
        foreach ($se_tag_raw as $row) {
            ?>
            <tr>
                <td>タグ</td>
                <td><?php $row['tag_name']; ?></td>
            </tr>
            <?php
        }
    }
    echo '</table>';
}else{
    echo '一致するものがありません';
}
?>

<a href="main.php">メインへ</a>