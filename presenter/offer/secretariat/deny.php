<?php
session_start();

require dirname(__FILE__) . '/../../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../../models/Company.php';

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getById($_POST['id']);
    PendingOffer::setStatus($offer->getId(), "Rejected");
}
header("Location: ../../../view/offer/list.php");
