<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('DB_HOST');      // Или впишите напрямую тот, что у вас в Railway
$port = getenv('DB_PORT');      // 3306
$user = getenv('DB_USER');      // root
$pass = getenv('DB_PASSWORD');      // пароль
$db   = getenv('DB_NAME');      // railway

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $stmt = $pdo->query("SELECT 1");
    $row = $stmt->fetch();
    echo "Connection SUCCESS: " . var_export($row, true);
} catch (PDOException $e) {
    echo "Connection FAILED: " . $e->getMessage();
}
