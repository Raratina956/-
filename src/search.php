<?php
require 'parts/auto-login.php';
$search_text = $_POST['search'];
unset($dis);
$user_data = [];
$judge = 0;
// 条件指定なし検索ユーザー
$search_all_u = $pdo->prepare('SELECT * FROM Users WHERE user_name LIKE ?');
$search_all_u->execute(['%' . $search_text . '%']);
$search_all_u_re = $search_all_u->fetchAll(PDO::FETCH_ASSOC);
if ($search_all_u_re) {
    // 条件指定なし検索ユーザー(結果あり)
    foreach ($search_all_u_re as $search_all_u_row) {
        $user_data[] = [
            'type' => 'user',
            'id' => $search_all_u_row['user_id'],
            'name' => $search_all_u_row['user_name']
        ];
    }
}
// 条件指定なし検索タグ
$search_all_t = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_name LIKE ?');
$search_all_t->execute(['%' . $search_text . '%']);
$search_all_t_re = $search_all_t->fetchAll(PDO::FETCH_ASSOC);
if ($search_all_t_re) {
    // 条件指定なし検索タグ(結果あり)
    foreach ($search_all_t_re as $search_all_t_row) {
        $tag_data[] = [
            'type' => 'tag',
            'id' => $search_all_t_row['tag_id'],
            'name' => $search_all_t_row['tag_name']
        ];
    }
}
?>
<?php
require 'header.php';
echo '<link rel="stylesheet" href="css/search.css">';
?>
<main>
    <h1>検索結果</h1>
    <h2><?php echo $search_text; ?></h2>
    <table>
        <tr>
            <th>種類</th>
            <th>ID</th>
            <th>名前</th>
        </tr>
        <?php
        if (isset($user_data) && !empty($user_data)) {
            foreach ($user_data as $data) {
                echo '<tr>';
                echo '<td>', $data['type'], '</td>';
                echo '<td>', $data['id'], '</td>';
                echo '<td>', $data['name'], '</td>';
                echo '</tr>';
            }
        } else {
            $judge = 1;
        }
        if (isset($tag_data) && !empty($tag_data)) {
            foreach ($tag_data as $data) {
                echo '<tr>';
                echo '<td>', $data['type'], '</td>';
                echo '<td>', $data['id'], '</td>';
                echo '<td>', $data['name'], '</td>';
                echo '</tr>';
            }
        } else {
            $judge = 1;
        }
        if ($judge == 1) {
            echo '<td colspan="3">検索結果なし</td>';
        }
        ?>
    </table>

</main>