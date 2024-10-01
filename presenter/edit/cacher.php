<?php

require dirname(__FILE__) . '/../../models/Offer.php';

if ($_POST['id']) {
    Offer::cacher($_POST['id']);
    header("Location: /view/edit/detail-company.php?id=". $_POST['id']);
}