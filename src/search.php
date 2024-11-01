<?php
require 'parts/auto-login.php';
require 'header.php';

$search_text = $_POST['search'] ?? '';
unset($dis);
$user_data = [];
$tag_data = [];
$judge = 0;

$kinds = $_POST['kinds'] ?? "a";
$method = $_POST['method'] ?? "part";

function limitDisplay($text, $limit = 10) {
    $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY); // 第3引数に -1 を指定
    return count($characters) > $limit ? implode('', array_slice($characters, 0, $limit)) . '...' : $text;
}

// User search
if ($kinds == "a" || $kinds == "u") {
    $search_all_u = $pdo->prepare(
        $method == "part" 
            ? 'SELECT * FROM Users WHERE user_name LIKE ?' 
            : 'SELECT * FROM Users WHERE user_name = ?'
    );
    $search_all_u->execute($method == "part" ? ['%' . $search_text . '%'] : [$search_text]);

    foreach ($search_all_u->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $user_data[] = ['type' => 'user', 'id' => $row['user_id'], 'name' => $row['user_name']];
    }
}

// Tag search
if ($kinds == "a" || $kinds == "t") {
    $search_all_t = $pdo->prepare(
        $method == "part" 
            ? 'SELECT Tag_list.*, Users.user_name AS creator_name FROM Tag_list LEFT JOIN Users ON Tag_list.user_id = Users.user_id WHERE tag_name LIKE ?' 
            : 'SELECT Tag_list.*, Users.user_name AS creator_name FROM Tag_list LEFT JOIN Users ON Tag_list.user_id = Users.user_id WHERE tag_name = ?'
    );
    $search_all_t->execute($method == "part" ? ['%' . $search_text . '%'] : [$search_text]);

    foreach ($search_all_t->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $tag_data[] = [
            'type' => 'tag',
            'id' => $row['tag_id'],
            'name' => $row['tag_name'],
            'creator_name' => $row['creator_name']
        ];
    }
}
?>

<link rel="stylesheet" href="css/search.css">
<main>
    <!-- Search Form -->
    <form action="search.php" method="post">
        <select class="sort-tag" name="kinds">
            <option value="a" <?= $kinds == "a" ? 'selected' : '' ?>>全て</option>
            <option value="u" <?= $kinds == "u" ? 'selected' : '' ?>>ユーザーのみ</option>
            <option value="t" <?= $kinds == "t" ? 'selected' : '' ?>>タグのみ</option>
        </select>
        
        <select class="sort-tag" name="method">
            <option value="all" <?= $method == "all" ? 'selected' : '' ?>>完全一致</option>
            <option value="part" <?= $method == "part" ? 'selected' : '' ?>>部分一致</option>
        </select>
        <br>
        <input type="text" class="search-text" name="search" value="<?= htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8') ?>" placeholder="検索したい内容を入力してください">
        <input class="search" type="submit" value="再検索">
    </form>
    
    <h2>【<?= htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8') ?>】の検索結果</h2>

    <!-- Search Results -->
    <div class="table-container">
        <table class="user-table">
            <tr><th colspan="2">ユーザー</th></tr>
            <tr class="h"><th></th><th>名前</th></tr>
            <?php if (!empty($user_data)) : ?>
                <?php foreach ($user_data as $data) : ?>
                    <form name="form<?= $data['id'] ?>" action="user.php" method="get">
                        <tr>
                            <?php
                            $iconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id = ?');
                            $iconStmt->execute([$data['id']]);
                            $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <td class="tag"><a href="javascript:document.form<?= $data['id'] ?>.submit()"><img src="<?= $icon['icon_name'] ?>" class="usericon"></a></td>
                            <input type="hidden" name="user_id" value="<?= $data['id'] ?>">
                            <td class="name"><a href="javascript:document.form<?= $data['id'] ?>.submit()"><h3><?= htmlspecialchars(limitDisplay($data['name']), ENT_QUOTES, 'UTF-8') ?></h3></a></td>
                        </tr>
                    </form>
                <?php endforeach; ?>
                <?php $judge = 1; ?>
            <?php endif; ?>
        </table>

        <table class="tag-table">
            <tr><th colspan="2">タグ</th></tr>
            <tr class="h"><th>作成者</th><th>タグ名</th></tr>
            <?php if (!empty($tag_data)) : ?>
                <?php foreach ($tag_data as $data) : ?>
                    <tr>
                        <td><h3><?= htmlspecialchars(limitDisplay($data['creator_name']), ENT_QUOTES, 'UTF-8') ?></h3></td>
                        <td><h3><?= htmlspecialchars(limitDisplay($data['name']), ENT_QUOTES, 'UTF-8') ?></h3></td>
                    </tr>
                <?php endforeach; ?>
                <?php $judge = 1; ?>
            <?php endif; ?>
        </table>
    </div>
</main>
<a href="main.php" class="back-link">メインへ</a>
