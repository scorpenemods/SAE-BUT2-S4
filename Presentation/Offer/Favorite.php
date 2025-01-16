<?php
// File: Favorite.php
// Make an Offer favorite
session_start();

require dirname(__FILE__) . '/../../Model/Offer.php';

$http_referer = $_SERVER["HTTP_REFERER"];
$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

if (isset($_SESSION['user']) && isset($id)) {
    $userId = $_SESSION['user_id'];
    if (Offer::is_favorite($_POST['id'], $userId)) {
        Offer::remove_favorite($_POST['id'], $userId);
    } else {
        Offer::make_favorite($_POST['id'], $userId);
    }
    die(json_encode(array("status" => "success")));
} else {
    die(json_encode(array("status" => "error")));
}
