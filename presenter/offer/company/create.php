<?php
session_start();
error_reporting(E_ALL ^ E_DEPRECATED);

require dirname(__FILE__) . "/../../../models/Company.php";

$user_id = $_SESSION['user'] ?? false;
$returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/view/offer/list.php";
if (!$user_id) {
    header("Location: " . $returnUrl);
    exit();
}

if (isset($_POST['name']) && isset($_POST['size']) && isset($_POST['address']) && isset($_POST['siren'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?? false;
    $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_STRING) ?? false;
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) ?? false;
    $siren = filter_input(INPUT_POST, 'siren', FILTER_SANITIZE_STRING) ?? false;

    if (!$name || !$size || !$address || !$siren) {
        header("Location: ../../../view/offer/company/create.php");
        exit();
    }

    // Create the company
    $company = Company::create($name, $size, $address, $siren, $user_id);

    // If the company is created, redirect to the list of companies
    if ($company) {
        header("Location: $returnUrl");
        exit();
    }
}