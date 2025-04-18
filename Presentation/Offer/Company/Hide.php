<?php
// File: Hide.php
// Hide a Company
session_start();

require dirname(__FILE__) . '/../../../Model/Offer.php';

if ((isset($_SESSION['company_id']) || isset($_SESSION['secretariat'])) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    Offer::hide($_POST['id']);
    header("Location: " . $_SERVER["HTTP_REFERER"]);
} else {
    header("Location: ../../../View/Offer/List.php");
}
