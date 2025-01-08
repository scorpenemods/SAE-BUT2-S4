<?php
// File: Reject.php
// Reject a pending Offer
session_start();

require dirname(__FILE__) . '/../../../Model/PendingOffer.php';
require dirname(__FILE__) . '/../../../Model/Company.php';

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::get_by_id($_POST['id']);
    PendingOffer::set_status($offer->get_id(), "Rejected");
}
header("Location: ../../../View/Offer/List.php");
