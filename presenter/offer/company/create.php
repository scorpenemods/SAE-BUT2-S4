<?php
session_start();

require dirname(__FILE__) . "/../../../models/Company.php";

$user_id = $_SESSION['user'] ?? false;
$http_referer = $_SERVER['HTTP_REFERER'] ?? false;
if (!$user_id && !$http_referer) {
    header("Location: ../../../offer/view/create.php");
    die();
}

if (isset($_POST['name']) && isset($_POST['size']) && isset($_POST['address']) && isset($_POST['siren'])) {
    $name = $_POST['name'];
    $size = $_POST['size'];
    $address = $_POST['address'];
    $siren = $_POST['siren'];

    //Create the company
    $company = Company::create($name, $size, $address, $siren, $user_id);

    //If the company is created, redirect to the list of companies
    if ($company) {
        header("Location: ../../../view/offer/list.php");
        die();
    }
}