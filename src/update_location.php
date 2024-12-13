<?php
require 'db-connect.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // JSONデータを受け取る
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['user_id'], $input['latitude'], $input['longitude'])) {
        throw new Exception("無効なリクエストデータ");
    }

    $user_id = $input['user_id'];
    $latitude = $input['latitude'];
    $longitude = $input['longitude'];
    $updated_at = date('Y-m-d H:i:s');

    // データベースに位置情報を保存
    $stmt = $pdo->prepare("
        INSERT INTO locations (user_id, latitude, longitude, updated_at) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            latitude = VALUES(latitude), 
            longitude = VALUES(longitude), 
            updated_at = VALUES(updated_at)
    ");
    $stmt->execute([$user_id, $latitude, $longitude, $updated_at]);

    echo json_encode(['success' => true, 'message' => '位置情報が保存']);

    
    // 以下通知処理
    // 現在時刻を取得
    $current_datetime = date('Y-m-d H:i:s');

    // 現在の位置情報を検索
    $info_search_sql = $pdo->prepare('SELECT * FROM Current_location WHERE user_id = ?');
    $info_search_sql->execute([$user_id]);
    $info_search_row = $info_search_sql->fetch();
    ?>
    <script>
        alert('テストA');
    </script>
    <?php
    if ($info_search_row) {
        ?>
    <script>
        alert('テストB');
    </script>
    <?php
        // 既存のデータがあれば更新
        $get_primary_key = $pdo->prepare('SELECT user_id FROM Current_location WHERE user_id = ?');
        $get_primary_key->execute([$user_id]);
        $current_location_id = $get_primary_key->fetchColumn();
        $info_update = $pdo->prepare('UPDATE Current_location SET classroom_id = ?, position_info_id = ?, logtime = ? WHERE user_id = ?');
        $info_update->execute([null, 1, $current_datetime, $user_id]);
        ?>
    <script>
        alert('テストC');
    </script>
    <?php
    } else {
        ?>
    <script>
        alert('テストD');
    </script>
    <?php
        // データがなければ挿入
        $info_insert = $pdo->prepare('INSERT INTO Current_location (user_id, position_info_id, logtime) VALUES (?, ?, ?)');
        $info_insert->execute([$user_id, 1, $current_datetime]);
        $current_location_id = $pdo->lastInsertId();
    }
    ?>
    <script>
        alert('テストE');
    </script>
    <?php
    $favorite_user = $pdo->prepare('SELECT * FROM Favorite WHERE follower_id=?');
    $favorite_user->execute([$_SESSION['user']['user_id']]);
    $favorite_results = $favorite_user->fetchAll(PDO::FETCH_ASSOC);
    if ($favorite_results) {
        foreach ($favorite_results as $favorite_row) {
            $announce_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=? AND type=?');
            $announce_sql->execute([
                $favorite_row['follow_id'],
                2
            ]);
            if ($announce_sql->rowCount() == 0) {
                $new_announce = $pdo->prepare('INSERT INTO Announce_check(current_location_id, user_id, read_check, type) VALUES (?, ?, ?, ?)');
                $new_announce->execute([$current_location_id, $favorite_row['follow_id'], 0, 2]);
            } else {
                $update_announce = $pdo->prepare('UPDATE Announce_check SET current_location_id=?, read_check=? WHERE user_id=? AND type=?');
                $update_announce->execute([$current_location_id, 0, $favorite_row['follow_id'], 2]);

            }
        }
    }

    // 成功レスポンスを返す
    echo json_encode(['success' => true, 'message' => 'DB保存が完了']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
    exit();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?>