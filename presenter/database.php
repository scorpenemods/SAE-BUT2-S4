<?php
global $db;

try {
    $db = new pdo('mysql:host=82.66.53.143:64893;dbname=offers_tables','sae','sae2024');
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