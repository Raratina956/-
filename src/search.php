<?php
require 'parts/auto-login.php';
$search_text = $_POST['search'];
unset($dis);
$user_data = [];
$tag_data = [];
$judge = 0;

if (isset($_POST['kinds'])) {
    $kinds = $_POST['kinds'];
} else {
    $kinds = "a";
}

if (isset($_POST['method'])) {
    $method = $_POST['method'];
} else {
    $method = "part";
}

// ユーザー検索
if ($kinds == "a" || $kinds == "u") {
    if ($method == "part") {
        $search_all_u = $pdo->prepare('SELECT * FROM Users WHERE user_name LIKE ?');
        $search_all_u->execute(['%' . $search_text . '%']);
    } else {
        $search_all_u = $pdo->prepare('SELECT * FROM Users WHERE user_name=?');
        $search_all_u->execute([$search_text]);
    }
    
    $search_all_u_re = $search_all_u->fetchAll(PDO::FETCH_ASSOC);
    if ($search_all_u_re) {
        foreach ($search_all_u_re as $search_all_u_row) {
            $user_data[] = [
                'type' => 'user',
                'id' => $search_all_u_row['user_id'],
                'name' => $search_all_u_row['user_name']
            ];
        }
    }
}

// タグ検索
if ($kinds == "a" || $kinds == "t") {
    if ($method == "part") {
        $search_all_t = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_name LIKE ?');
        $search_all_t->execute(['%' . $search_text . '%']);
    } else {
        $search_all_t = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_name=?');
        $search_all_t->execute([$search_text]);
    }
    
    $search_all_t_re = $search_all_t->fetchAll(PDO::FETCH_ASSOC);
    if ($search_all_t_re) {
        foreach ($search_all_t_re as $search_all_t_row) {
            $tag_data[] = [
                'type' => 'tag',
                'id' => $search_all_t_row['tag_id'],
                'name' => $search_all_t_row['tag_name']
            ];
        }
    }
}
?>

<?php
require 'header.php';
echo '<link rel="stylesheet" href="css/search.css">';
?>
<main>
   
    <!-- Search Form -->
    <form action="search.php" method="post">
    <select class="sort-tag" name="kinds">
            <option value="a" <?php if ($kinds == "a") echo 'selected'; ?>>全て</option>
            <option value="u" <?php if ($kinds == "u") echo 'selected'; ?>>ユーザーのみ</option>
            <option value="t" <?php if ($kinds == "t") echo 'selected'; ?>>タグのみ</option>
        </select>
        
        <select class="sort-tag"name="method">
            <option value="all" <?php if ($method == "all") echo 'selected'; ?>>完全一致</option>
            <option value="part" <?php if ($method == "part") echo 'selected'; ?>>部分一致</option>
        </select>
        <br>
        <input type="text" class="search-text" name="search" value="<?php echo htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8'); ?>" placeholder="検索したい内容を入力してください">
        
        
        <input class="search" type="submit" value="再検索">
    </form>
    
    <h2>【<?php echo htmlspecialchars($search_text, ENT_QUOTES, 'UTF-8'); ?>】の検索結果</h2>
    
    <!-- Search Results -->
    <table>
        <tr>
            <th>種類</th>
            <th >名前</th>
        </tr>
        <?php
        if (!empty($user_data)) {
            foreach ($user_data as $data) {
                echo '<tr>';
                echo '<td class="tag"><img src="img/icon/default.jpg" width="40%"height="40%"></td>';
                echo '<td class="name"><h2>', htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'), '</h2></td>';
                echo '</tr>';
            }
            $judge = 1;
        }

        if (!empty($tag_data)) {
            foreach ($tag_data as $data) {
                echo '<tr>';
                echo '<td class="tag">タグ</td>';
                echo '<td class="name"><h2>', htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'), '</h2></td>';
                echo '</tr>';
            }
            $judge = 1;
        }

        if ($judge == 0) {
            echo '<tr><td colspan="2">検索結果なし</td></tr>';
        }
        ?>
    </table>
</main>
