<?php
session_start();

require dirname(__FILE__) . '/../../models/Offer.php';

$http_referer = $_SERVER["HTTP_REFERER"];

if (isset($_SESSION['user']) && isset($_GET['id'])) {
    $user_id = $_SESSION['user'];
    if (Offer::isFavorite($_GET['id'], $user_id)) {
        Offer::removeFavorite($_GET['id'], $user_id);
        header($http_referer);
        die();
    } else {
        Offer::makeFavorite($_GET['id'], $user_id);
        header($http_referer);
        die();
    }
} else {
    header($http_referer);
    die();
}