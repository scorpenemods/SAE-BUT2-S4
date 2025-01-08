<?php
// File: Validate.php
// Validate an application
session_start();

require '../../../Model/Applications.php';
$httpReferer = $_SERVER["HTTP_REFERER"];

if ((isset($_SESSION['company']) || isset($_SESSION['secretariat'])) && isset($_POST['idOffer'])) {
    $user_id = $_SESSION['user'];
    Applications::validate($_POST['idOffer']);
    header("Location: " . $httpReferer);
    echo $httpReferer;
    die();
}

