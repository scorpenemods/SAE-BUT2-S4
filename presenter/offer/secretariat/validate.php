<?php

if (isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getById($_POST['id']);

    if ($offer->getId() == 0) {
        Offer::create($offer->getCompanyId(), $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), true, $offer->getEducation(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getFileName(), $offer->getFileType(), $offer->getFileSize());
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    } else {
        // TODO: Update offer
    }
}