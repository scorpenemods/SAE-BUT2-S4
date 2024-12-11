<?php
session_start();

require dirname(__FILE__) . '/../../models/Offer.php';

$http_referer = $_SERVER["HTTP_REFERER"];
$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

if (isset($_SESSION['user']) && isset($id)) {
    $user_id = $_SESSION['user'];
    if (Offer::isFavorite($_POST['id'], $user_id)) {
        Offer::removeFavorite($_POST['id'], $user_id);
    } else {
        Offer::makeFavorite($_POST['id'], $user_id);
    }
    die(json_encode(array("status" => "success")));
} else {
    die(json_encode(array("status" => "error")));
}
