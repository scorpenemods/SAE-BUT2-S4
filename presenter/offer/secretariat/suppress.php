<?php
session_start();

require dirname(__FILE__) . '/../../../models/Offer.php';

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    Offer::suppress($_POST['id']);
}

header("Location: ../../../view/offer/list.php");
