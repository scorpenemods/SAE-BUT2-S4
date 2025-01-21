<?php
// File: Refuse.php
// Refuse an application
session_start();

require_once dirname(__FILE__) . '/../../../Model/Offer.php';
$db = Database::getInstance()->getConnection();
$httpReferer = $_SERVER["HTTP_REFERER"];

if ((isset($_SESSION['company_id']) || isset($_SESSION['secretariat'])) && isset($_POST['id_offer'])) {
    $user_id = $_SESSION['user_id'];
    Application::refuse($_POST['id_offer']);
    header("Location: " . $httpReferer);
    echo $httpReferer;
    die();
}

