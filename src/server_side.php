<?php
header("Content-type: text/plain; charset=UTF-8");

// POST変数を取得
$date = $_POST['date'];
$lat = $_POST['lat'];
$lon = $_POST['lon'];
$alt = $_POST['alt'];
$posacc = $_POST['posacc'];
$altacc = $_POST['altacc'];
$head = $_POST['head'];
$speed = $_POST['speed'];

// csv保存
$file = fopen("position.csv", "a");
fwrite($file, "$date,$lat,$lon,$alt,$posacc,$altacc,$head,$speed");
fwrite($file, "\n");
fclose($file);

// フロントサイドへてきとーに返しておく
echo "これもらったよ。$date,$lat,$lon,$alt,$posacc,$altacc,$head,$speed";