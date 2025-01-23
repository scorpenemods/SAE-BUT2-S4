<?php
/*
 * database.php
 * Contains the database connection & instance variable.
 */

global $db;

try {
    if (!isset($db)) {
        $db = new pdo('mysql:host=db.liruz.fr:3306;dbname=offers_tables','sae','sae2024', array(PDO::ATTR_PERSISTENT => true));
    }
} catch(PDOException $ex) {
    echo $ex->getMessage();
    exit(json_encode(array('outcome' => false, 'message' => 'Unable to connect')));
}