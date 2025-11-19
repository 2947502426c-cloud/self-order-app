<?php
date_default_timezone_set('Asia/Hong_Kong');
error_reporting(E_ALL & ~E_WARNING);
require_once("database.php");
header("Content-Type: text/plain; charset=utf-8");

// 驗證POST數據
if (!isset($_POST["tableNo"], $_POST["clientNo"], $_POST["timeLength"])) {
    echo "參數錯誤";
    exit;
}

$tableNo = $_POST["tableNo"];
$clientNo = $_POST["clientNo"];
$timeLength = $_POST["timeLength"];
$timeStart = date("Y-m-d H:i:s");
$timeEnd = date("Y-m-d H:i:s", strtotime("+$timeLength minutes"));
$token = uniqid() . rand(1000, 9999); // 確保token唯一

// 插入用餐信息到newClient表（若表不存在，先執行創建SQL）
try {
    $sql = "INSERT INTO newClient (tableNo, clientNo, timeStart, timeEnd, token) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->execute([$tableNo, $clientNo, $timeStart, $timeEnd, $token]);
    // 生成正確的URL（確保包含self-order文件夾）
    echo "http://self-order-app-gray.vercel.app/step3.php?tk=" . $token;
} catch (PDOException $e) {
    echo "錯誤：" . $e->getMessage();
}

$con = null;

?>
