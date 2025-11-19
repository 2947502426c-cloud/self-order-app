<?php
date_default_timezone_set('Asia/Hong_Kong');
// AppServ MySQL配置：用戶名默認root，密碼填安裝時設置的（沒設則留空）
$host = 'localhost';
$dbname = 'order_db';
$username = 'root';
$password = '2947502426'; // 重點：替換為你的MySQL密碼

try {
    $con = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("數據庫連接失敗：" . $e->getMessage());
}
?>