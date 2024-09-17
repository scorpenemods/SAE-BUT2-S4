<?php
$dsn = "mysql:host=localhost;dbname=dbsae;charset=utf8";
$username = "scorpene";
$password = "8172";

try {
    $pdo = new PDO($dsn, $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

#$password = 'sae';
#$password_hash = password_hash($password, PASSWORD_BCRYPT);
#echo $password_hash;

?>