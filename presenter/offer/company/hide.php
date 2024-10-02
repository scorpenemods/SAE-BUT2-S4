<?php

require dirname(__FILE__) . '/../../../models/Offer.php';

if (isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    Offer::hide($_POST['id']);
    header("Location: " . $_SERVER["HTTP_REFERER"]);
}