<?php
session_start();

require '../../../models/Applications.php';
$http_referer = $_SERVER["HTTP_REFERER"];

if ((isset($_SESSION['company']) || isset($_SESSION['secretariat'])) && isset($_POST['id_offer'])) {
    $user_id = $_SESSION['user'];
    Applications::refuse($_POST['id_offer']);
    header("Location: " . $http_referer);
    echo $http_referer;
    exit();
}

