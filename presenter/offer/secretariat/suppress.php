<?php
session_start();

require dirname(__FILE__) . '/../../../models/Offer.php';

$returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/view/offer/list.php";

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    Offer::suppress($_POST['id']);
}

header("Location: " . $returnUrl);
