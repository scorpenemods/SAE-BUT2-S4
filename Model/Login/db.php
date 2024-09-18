<?php
$host = 'localhost';
$port = '3306';
$db = 'saeDB';
$user = 'root';
$pass = 'loptro342004';

try {
    $pdo = new PDO($host,$host, $user, $pass);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

#$password = 'sae';
#$password_hash = password_hash($password, PASSWORD_BCRYPT);
#echo $password_hash;

/* Noah connection DB
$dsn = "mysql:host=localhost;dbname=dbsae;charset=utf8";
$username = "scorpene";
$password = "8172";
*/

?>