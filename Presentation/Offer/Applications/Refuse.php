<?php
// File: Refuse.php
// Refuse an application
session_start();

require '../../../Model/Application.php';
$httpReferer = $_SERVER["HTTP_REFERER"];

if ((isset($_SESSION['company']) || isset($_SESSION['secretariat'])) && isset($_POST['idOffer'])) {
    $user_id = $_SESSION['user'];
    Application::refuse($_POST['idOffer']);
    header("Location: " . $httpReferer);
    echo $httpReferer;
    die();
}

