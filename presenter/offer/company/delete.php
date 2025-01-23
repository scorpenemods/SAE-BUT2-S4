<?php
session_start();

require dirname(__FILE__) . '/../../../models/Company.php';

$http_referer = $_SERVER["HTTP_REFERER"];

if (isset($_SESSION['secretariat']) && isset($_POST['company_id'])) {
    $boolean = Company::delete($_POST['company_id']);
    if ($boolean) {
        exit(json_encode(array("status" => "success")));
    } else {
        exit(json_encode(array("status" => "error")));
    }
} else {
    exit(json_encode(array("status" => "error")));
}
