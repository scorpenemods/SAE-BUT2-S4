<?php
session_start();

require '../../../models/Applications.php';

$returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/view/offer/list.php";
$http_referer = $_SERVER["HTTP_REFERER"];

if ((isset($_SESSION['company']) || isset($_SESSION['secretariat'])) && isset($_POST['id_offer'])) {
    $user_id = $_SESSION['user'];
    Applications::validate($_POST['id_offer']);
    header("Location: " . $http_referer);
    echo $http_referer;
    die();
} else {
    header("Location: " . $returnUrl);
}
