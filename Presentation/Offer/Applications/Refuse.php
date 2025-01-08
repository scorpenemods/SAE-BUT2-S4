<?php
// File: Refuse.php
// Refuse an application
session_start();

require '../../../Model/Applications.php';
$httpReferer = $_SERVER["HTTP_REFERER"];

if ((isset($_SESSION['company']) || isset($_SESSION['secretariat'])) && isset($_POST['idOffer'])) {
    $user_id = $_SESSION['user'];
    Applications::refuse($_POST['idOffer']);
    header("Location: " . $httpReferer);
    echo $httpReferer;
    die();
}

