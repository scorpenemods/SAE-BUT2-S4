<?php
// File: Notify.php
// Notify a pending Offer
session_start();

require dirname(__FILE__) . '/../../../Model/PendingOffer.php';
require dirname(__FILE__) . '/../../../Model/Company.php';
require dirname(__FILE__) . '/../../../Presentation/Offer/Notify.php';

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::get_by_offer_id($_POST['id']);
    if ($offer->get_status() == "Pending") {
        if ($offer->get_offer_id() == 0) {
            $company_id = $offer->get_company_id();
            $offer_notify = Offer::create($company_id, $offer->get_title(), $offer->get_description(), $offer->get_job(), $offer->get_duration(), $offer->get_salary(), $offer->get_address(), $offer->get_study_level(), $offer->get_begin_date(), $offer->get_tags(), $offer->get_email(), $offer->get_phone(), $offer->get_website(), $offer->get_latitude(), $offer->get_longitude());
            send_notification($offer_notify);
            error_log("apres l'appel de sendNotification");
        } else {
            Offer::update($offer->get_offer_id(), $offer->get_title(), $offer->get_description(), $offer->get_job(), $offer->get_duration(), $offer->get_salary(), $offer->get_address(), $offer->get_study_level(), $offer->get_begin_date(), $offer->get_tags(), $offer->get_email(), $offer->get_phone(), $offer->get_website(), $offer->get_latitude(), $offer->get_longitude());
        }
        PendingOffer::set_status($offer->get_id(), "Accepted");
    }
}
header("Location: ../../../View/Offer/List.php?type=all");