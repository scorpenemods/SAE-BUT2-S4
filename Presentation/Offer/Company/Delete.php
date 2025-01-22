<?php
// File: Delete.php
// Delete a Company
session_start();

require dirname(__FILE__) . '/../../../Model/Company.php';

if (isset($_SESSION['secretariat']) && isset($_POST['company_id'])) {
    $boolean = Company::delete($_POST['company_id']);
    /*
    if ($boolean) {
        die(json_encode(array("status" => "success")));
    } else {
        die(json_encode(array("status" => "error")));
    }
    */
    die(json_encode(array("status" => "success")));
} else {
    die(json_encode(array("status" => "error")));
}
