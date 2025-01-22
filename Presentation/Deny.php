<?php
// reject an offer
session_start();

require '../Model/PendingOffer.php';
require '../Model/Company.php';

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getById($_POST['id']);
    PendingOffer::setStatus($offer->getId(), "Rejected");
}

header("Location: ../View/List.php");
