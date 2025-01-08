<?php
// File: Delete.php
// Delete a Company
session_start();

require dirname(__FILE__) . '/../../../Model/Company.php';

$httpReferer = $_SERVER["HTTP_REFERER"];

if (isset($_SESSION['secretariat']) && isset($_POST['companyId'])) {
    $boolean = Company::delete($_POST['companyId']);
    if ($boolean) {
        die(json_encode(array("status" => "success")));
    } else {
        die(json_encode(array("status" => "error")));
    }
} else {
    die(json_encode(array("status" => "error")));
}
