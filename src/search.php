<?php
require 'parts/auto-login.php';
require 'header.php';
if (isset($_POST['join_tag_id'])) {
    $regi_tag_id = $_POST['join_tag_id'];
    $sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
    $sql->execute([$regi_tag_id, $_SESSION['user']['user_id']]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $sql_insert = $pdo->prepare('INSERT INTO Tag_attribute (tag_id,user_id) VALUES (?,?)');
        $sql_insert->execute([
            $regi_tag_id,
            $_SESSION['user']['user_id']
        ]);
    } else {
        $sql_delete = $pdo->prepare('DELETE FROM Tag_attribute WHERE tag_id=? AND user_id=?');
        $sql_delete->execute([$regi_tag_id, $_SESSION['user']['user_id']]);
    }
}
$search_text = $_POST['search'] ?? '';
unset($dis);
$user_data = [];
$tag_data = [];
$judge = 0;

$kinds = $_POST['kinds'] ?? "a";
$method = $_POST['method'] ?? "part";

function limitDisplay($text, $limit = 10)
{
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
<link rel="stylesheet" href="mob_css/search-mob.css" media="screen and (max-width: 480px)">
<link rel="stylesheet" href="css/search.css" media="screen and (min-width: 1280px)">
<!-- font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic&display=swap" rel="stylesheet">

<main>
    <!-- Search Form -->
    <form action="search.php" method="post">
        <select class="sort-tag" name="kinds">
            <option value="a" <?php if ($kinds == "a")
                echo 'selected'; ?>>全て</option>
            <option value="u" <?php if ($kinds == "u")
                echo 'selected'; ?>>ユーザーのみ</option>
            <option value="t" <?php if ($kinds == "t")
                echo 'selected'; ?>>タグのみ</option>
        </select>

        <select class="sort-tag" name="method">
            <option value="all" <?php if ($method == "all")
                echo 'selected'; ?>>完全一致</option>
            <option value="part" <?php if ($method == "part")
                echo 'selected'; ?>>部分一致</option>
        </select>
        <br>
        <input type="text" class="search-text" name="search"
            value="<?php echo htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8'); ?>" placeholder="検索したい内容を入力してください">
        <input class="search" type="submit" value="再検索">
    </form>

    <h2>【<?php echo htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8'); ?>】の検索結果</h2>

    <div class="table-container">
        <?php if (!empty($user_data)): ?>
            <table class="user-table">
                <tr>
                    <th colspan="2">ユーザー</th>
                </tr>
                <tr class="h">
                    <th></th>
                    <th>名前</th>
                </tr>
                <?php foreach ($user_data as $data): ?>
                    <form name="form<?php echo $data['id']; ?>" action="user.php" method="get">
                        <?php
                        $iconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id=?');
                        $iconStmt->execute([$data['id']]);
                        $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <tr>
                        <td class="icon tag"><a href="javascript:document.form<?php echo $data['id']; ?>.submit()">
                                    <!-- <?php
                                    if ($data['s_or_t'] == 1) {
                                        echo '<img src="kakubo.jpg" class="hat">';
                                    }
                                    ?> -->
                                    <img src="<?php echo $icon['icon_name']; ?>" class="usericon"></a></td>
                            <input type="hidden" name="user_id" value="<?php echo $data['id']; ?>">
                            <td class="name"><a href="javascript:document.form<?php echo $data['id']; ?>.submit()">

                                    <h3><?php echo htmlspecialchars(limitDisplay($data['name'], 10), ENT_QUOTES, 'UTF-8'); ?>
                                    </h3>


                                </a></td>
                        </tr>
                    </form>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if (!empty($tag_data)): ?>
            <table class="tag-table">
                <tr>
                    <th colspan="2">タグ</th>
                </tr>
                <tr class="h">
                    <th>タグ名</th>
                    <th></th>
                </tr>
                <?php foreach ($tag_data as $data): ?>
                    <tr>
                        <td>
                            <h3><?php echo htmlspecialchars(limitDisplay($data['name'], 10), ENT_QUOTES, 'UTF-8'); ?></h3>
                        </td>
                        <form action="search.php" method="post">
                            <input type="hidden" name="join_tag_id" value=<?php echo $data['id']; ?>>
                            <?php
                            if (isset($_POST['search'])) {
                                echo '<input type="hidden" name="search" value="', $_POST['search'], '">';
                            }
                            $sql_tag = $pdo->prepare('SELECT * FROM Tag_attribute WHERE tag_id=? AND user_id=?');
                            $sql_tag->execute([$data['id'], $_SESSION['user']['user_id']]);
                            $row_tag = $sql_tag->fetch(PDO::FETCH_ASSOC);
                            if (!$row_tag) {
                                echo '<td><input type="submit" value="参加" class="join"></td>';
                            } else {
                                echo '<td><input type="submit" value="参加済" class="joined"></td>';
                            }
                            ?>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if (empty($user_data) && empty($tag_data)): ?>
            <p>検索結果がありません。</p>
        <?php endif; ?>
    </div>
</main>

<a href="map.php" class="back-link">戻る</a>