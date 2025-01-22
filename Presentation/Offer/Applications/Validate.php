<?php
// File: Validate.php
// Validate an application
session_start();

require '../../../Model/Application.php';
$httpReferer = $_SERVER["HTTP_REFERER"];

if ((isset($_SESSION['company_id']) || isset($_SESSION['secretariat'])) && isset($_POST['id_offer'])) {
    $user_id = $_SESSION['user'];
    Application::validate($_POST['id_offer']);
    header("Location: " . $httpReferer);
    echo $httpReferer;
    die();
}

