<?php
// File: Database.php
// Database connection

global $db;

try {
    if (!isset($db)) {
        $db = new pdo('mysql:host=db.liruz.fr:3306;dbname=book','sae','sae2024', array(PDO::ATTR_PERSISTENT => true));
    }
} catch(PDOException $ex) {
    echo $ex->getMessage();
    die(
        json_encode(
            array(
                'outcome' => false,
                'message' => 'Unable to connect'
            )
        )
    );
}
