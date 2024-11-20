<?php
session_start();

function sendNotification($offer){
    $database = (Database::getInstance());
    $alerts = $database->getAlert();

    forEach($alerts as $alert){
        $duration = $alert['duration'];

    }
}

