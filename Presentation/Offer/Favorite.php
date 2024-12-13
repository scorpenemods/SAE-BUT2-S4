<?php
session_start();

include_once '../../Model/Offer.php';

$http_referer = $_SERVER["HTTP_REFERER"];
echo $_SESSION["user_id"];
if (isset($_SESSION['user_id']) && isset($_POST['id'])) {
    $user_id = $_SESSION['user_id'];
    if (Offer::isFavorite($_POST['id'], $user_id)) {
        Offer::removeFavorite($_POST['id'], $user_id);
        die(json_encode(array("status" => "success")));
    } else {
        Offer::makeFavorite($_POST['id'], $user_id);
        die(json_encode(array("status" => "success")));
    }
} else {
    die(json_encode(array("status" => "error")));
}