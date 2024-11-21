<?php
session_start();

require dirname(__FILE__) . '/../../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../../models/Company.php';
require dirname(__FILE__) . '/../../../models/Database.php';
require dirname(__FILE__) . '/../../../presenter/offer/notify.php';




if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getByOfferId($_POST['id']);
    if ($offer->getStatus() == "Pending") {
        if ($offer->getOfferId() == 0) {
            $company_id = $offer->getCompanyId();
            $offer_notify = Offer::create($company_id, $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getWebsite());
            sendNotification($offer_notify);
            error_log("apres lappel de sendNotification");
        } else {
            Offer::update($offer->getOfferId(), $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getWebsite());
        }
        PendingOffer::setStatus($offer->getId(), "Accepted");
    }
}
header("Location: ../../../view/offer/list.php?type=all");