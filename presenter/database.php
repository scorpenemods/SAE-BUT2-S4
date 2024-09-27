<?php
global $db;

try {
    $db = new pdo('mysql:host=mysql:3306;dbname=app_notes','root','root');
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
?>