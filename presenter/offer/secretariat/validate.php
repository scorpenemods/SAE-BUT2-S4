<?php

require dirname(__FILE__) . '/../../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../../models/Company.php';

if (isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getById($_POST['id']);
    if ($offer->getOfferId() == 0) {
        $company_id = $offer->getCompanyId();
        Offer::create($company_id, $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), '', '', 0);
        PendingOffer::setStatus($offer->getId(), "Accepted");
    } else {
        Offer::update($offer->getOfferId(), $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), '', '', 0);
        PendingOffer::setStatus($offer->getId(), "Accepted");
    }
}