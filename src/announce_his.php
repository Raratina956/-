<?php
require 'parts/auto-login.php';


?>
<h1>アナウンス送信履歴</h1>
<p>
    <?php
    $ann_list_sql = $pdo->prepare('SELECT * FROM Notification WHERE send_person=?');
    $ann_list_sql->execute([$_SESSION['user']['user_id']]);
    $ann_list_row = $ann_list_sql->fetchAll(PDO::FETCH_ASSOC);
    if ($ann_list_row) {
        ?>
    <table>
        <th></th>
        <th>タイトル</th>
        <th>送信者</th>
        <th>送信先</th>
        <th>送信日時</th>
        <th></th>
        <?php
        foreach ($ann_list_row as $ann_row) {
            $announcement_id = $ann_row['announcement_id'];
            $title = $ann_row['title'];
            $content = $ann_row['content'];
            $send_user_id = $ann_row['send_person'];
            $sent_tag_id = $ann_row['sent_tag'];
            $send_time = $ann_row['sending_time'];
            $ann_type="1";
            $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
            $user_sql->execute([$send_user_id]);
            $user_row = $user_sql->fetch(PDO::FETCH_ASSOC);
            $send_user_name = $user_row['user_name'];
            $tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
            $tag_sql->execute([$sent_tag_id]);
            $tag_row = $tag_sql->fetch(PDO::FETCH_ASSOC);
            $sent_tag_name = $tag_row['tag_name'];
            ?>
            <tr>
                <td>
                    <?php
                        switch ($type) {
                            case 1:
                                echo "送信";
                                break;
                            case 2:
                                echo "受信";
                                break;
                            default:
                                echo "エラー";
                                break;
                        }
                    ?>
                </td>
                <td><?php echo $title; ?></td>
                <td><?php echo $send_user_name; ?></td>
                <td><?php echo $sent_tag_name; ?></td>
                <td><?php echo $send_time; ?></td>
                <form action="announce_his_info.php" method="post">
                    <input type="hidden" name="announcement_id" value=<?php echo $announcement_id; ?>>
                    <td><input type="submit" value="詳細"></td>
                </form>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    } else {
        echo '<span>送信したアナウンスがありません</span>';
    }
    ?>
</p>
<?php




?>