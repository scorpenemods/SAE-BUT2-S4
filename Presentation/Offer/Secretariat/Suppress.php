<?php
// File: Suppress.php
// Suppress an Offer
session_start();

require dirname(__FILE__) . '/../../../Model/Offer.php';

if (isset($_SESSION['Secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    Offer::suppress($_POST['id']);
}

header("Location: ../../../View/Offer/List.php");
