<?php
// File: Create.php
// Create a Company
session_start();

require dirname(__FILE__) . "/../../../Model/Company.php";

$userId = $_SESSION['user'] ?? false;
$httpReferer = $_SERVER['HTTP_REFERER'] ?? false;
if (!$userId && !$httpReferer) {
    header("Location: ../../../View/Offer/List.php");
    die();
}

error_reporting(E_ALL ^ E_DEPRECATED);
if (isset($_POST['name']) && isset($_POST['size']) && isset($_POST['address']) && isset($_POST['siren'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?? false;
    $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_STRING) ?? false;
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) ?? false;
    $siren = filter_input(INPUT_POST, 'siren', FILTER_SANITIZE_STRING) ?? false;

    if (!$name || !$size || !$address || !$siren) {
        header("Location: ../../../View/Offer/Company/Create.php");
        die();
    }
    //Create the Company
    $company = Company::create($name, $size, $address, $siren);

    //If the Company is created, redirect to the list of companies
    if ($company) {
        header("Location: ../../../View/Offer/List.php");
        die();
    }
}