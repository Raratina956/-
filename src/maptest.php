<?php
require 'parts/auto-login.php';
require 'header.php';
unset($_SESSION['floor']['kai']);

// 定数の定義を確認
if (!defined('SERVER')) {
    define('SERVER', 'your_server');
}
if (!defined('DBNAME')) {
    define('DBNAME', 'your_dbname');
}
if (!defined('USER')) {
    define('USER', 'your_username');
}
if (!defined('PASS')) {
    define('PASS', 'your_password');
}

// データベース接続
require 'db-connect.php';

// ユーザーIDを取得
$user_id = $_SESSION['user']['user_id'];

// ユーザーの位置情報を取得
$sql_locations = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
$sql_locations->execute([$user_id]);
$location_data = $sql_locations->fetchAll(PDO::FETCH_ASSOC);

$locations = [];
foreach ($location_data as $location) {
    // カラムの存在をチェック
    if (isset($location['x_coordinate'], $location['y_coordinate'], $location['z_coordinate'])) {
        $locations[] = [
            'x' => (float)$location['x_coordinate'],
            'y' => (float)$location['y_coordinate'],
            'z' => (float)$location['z_coordinate']
        ];
    } else {
        echo "位置情報が不完全です。";
    }
}

$results = $sql->fetchAll(PDO::FETCH_ASSOC);

// 位置情報を取得
$locations = [];
$sql_locations = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
$sql_locations->execute([$user_id]);
$location_data = $sql_locations->fetchAll(PDO::FETCH_ASSOC);

// 位置情報をJavaScript用に整形
foreach ($location_data as $location) {
    $locations[] = [
        'x' => (float)$location['x_coordinate'], // x座標
        'y' => (float)$location['y_coordinate'], // y座標
        'z' => (float)$location['z_coordinate']  // z座標
    ];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mob_css/map-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/map.css" media="screen and (min-width: 1280px)">
    <title>3D MAP</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
</head>
<body>

<div class="map">
    <h1 class="title">麻生情報ビジネス専門学校 3Dマップ</h1>
    
    <script>
        // PHPから位置情報をJavaScriptに渡す
        const locations = <?php echo json_encode($locations); ?>;

        // シーンを作成
        const scene = new THREE.Scene();

        // カメラを作成
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 5;

        // レンダラーを作成
        const renderer = new THREE.WebGLRenderer();
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.body.appendChild(renderer.domElement);

        // ライトを追加
        const light = new THREE.AmbientLight(0xffffff); // 白色の光
        scene.add(light);

        // 立方体のジオメトリを作成し、位置情報をもとにオブジェクトを追加
        locations.forEach(location => {
            const geometry = new THREE.BoxGeometry(1, 1, 1);
            const material = new THREE.MeshBasicMaterial({ color: Math.random() * 0xffffff });
            const cube = new THREE.Mesh(geometry, material);
            cube.position.set(location.x, location.y, location.z); // 座標に基づいて配置
            scene.add(cube);
        });

        // アニメーションループ
        function animate() {
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
        }
        animate();

        // リサイズ対応
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    </script>
</div>

<div class="gakugai-container">
    <h2><a href="mapindex.php">学外</a></h2>
</div>
<br>
<br>

</body>
</html>
