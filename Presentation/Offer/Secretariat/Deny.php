<?php
session_start();

require dirname(__FILE__) . '/../../../Model/PendingOffer.php';
require dirname(__FILE__) . '/../../../Model/Company.php';

if (isset($_SESSION['Secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getById($_POST['id']);
    PendingOffer::setStatus($offer->getId(), "Rejected");
}
header("Location: ../../../View/List.php    ");
