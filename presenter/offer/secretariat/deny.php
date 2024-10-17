<?php

require dirname(__FILE__) . '/../../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../../models/Company.php';

if (isset($_SESSION['secretariat']) && isset($_GET['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getById($_GET['id']);
    PendingOffer::setStatus($offer->getId(), "Rejected");
}
header("Location: ../../../view/offer/list.php");
