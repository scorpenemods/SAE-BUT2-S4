<?php
session_start();

require dirname(__FILE__) . "/../../../models/Company.php";

$user_id = $_SESSION['user'] ?? false;
$http_referer = $_SERVER['HTTP_REFERER'] ?? false;
if (!$user_id && !$http_referer) {
    header("Location: ../../../offer/view/create.php");
    die();
}

error_reporting(E_ALL ^ E_DEPRECATED);
if (isset($_POST['name']) && isset($_POST['size']) && isset($_POST['address']) && isset($_POST['siren'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $siren = filter_input(INPUT_POST, 'siren', FILTER_SANITIZE_STRING);

    //Create the company
    $company = Company::create($name, $size, $address, $siren, $user_id);

    //If the company is created, redirect to the list of companies
    if ($company) {
        header("Location: ../../../view/offer/list.php");
        die();
    }
}