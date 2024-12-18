<?php
session_start();

require dirname(__FILE__) . '/../../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../../models/Company.php';
require dirname(__FILE__) . '/../../../models/Database.php';
require dirname(__FILE__) . '/../../../presenter/offer/notify.php';

function getAddress($offer) {
    $address = $offer->getAddress();
    //fetch the latitude and longitude from an API
    $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($address) . "&format=json";
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    if (isset($data[0]["lat"]) && isset($data[0]["lon"])) {
        $latitude = $data[0]["lat"];
        $longitude = $data[0]["lon"];
    } else {
        $latitude = 0;
        $longitude = 0;
    }
    return [$latitude, $longitude];
}

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getByOfferId($_POST['id']);
    if ($offer->getStatus() == "Pending") {
        if ($offer->getOfferId() == 0) {
            $company_id = $offer->getCompanyId();
            $coordinates = getAddress($offer);
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];
            $offer_notify = Offer::create($company_id, $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getWebsite(), $latitude, $longitude);
            sendNotification($offer_notify);
            error_log("apres lappel de sendNotification");
        } else {
            Offer::update($offer->getOfferId(), $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getWebsite());
        }
        PendingOffer::setStatus($offer->getId(), "Accepted");
    }
}
header("Location: ../../../view/offer/list.php?type=all");