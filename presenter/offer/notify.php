<?php
session_start();

$database = (Database::getInstance());
$alerts = $database->getAlert();

$offerp = PendingOffer::getByOfferId($_POST['id']);
$offer = $offerp->getOfferId();

forEach($alerts as $alert){
}