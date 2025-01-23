<?php
/*
 * validate.php
 * Allows the secretariat to validate an offer.
 */

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/models/PendingOffer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/models/Company.php';
require $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/presenter/offer/notify.php';

$returnUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/view/offer/list.php";

function getCoordinates($address): ?array {
    $base_url = "https://nominatim.openstreetmap.org/search";
    $params = [
        'q' => $address,
        'format' => 'json',
        'limit' => 1
    ];
    $options = [
        'http' => [
            'header' => "User-Agent: YourApp/1.0 (your@email.com)\r\n"
        ]
    ];

    $context = stream_context_create($options);
    $url = $base_url . '?' . http_build_query($params);

    try {
        $response = file_get_contents($url, false, $context);
        $http_response_header = $http_response_header ?? [];

        $data = json_decode($response, true);
        $status_code = intval(substr($http_response_header[0], 9, 3));
        if ($status_code != 200 || !$data) {
            error_log("Error fetching coordinates: " . $response);

            return null;
        }

        return [floatval($data[0]['lat']), floatval($data[0]['lon'])];
    } catch (Exception $e) {
        error_log("Error fetching coordinates: " . $e->getMessage());

        return null;
    }
}

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getByOfferId($_POST['id']);
    if ($offer->getStatus() == "Pending") {
        if ($offer->getOfferId() == 0) {
            $company_id = $offer->getCompanyId();
            $coordinates = getCoordinates($offer->getAddress());
            if (!$coordinates) {
                header("Location: " . $returnUrl . "?notification=failure/Erreur/Coordonnées+manquantes");
                exit();
            }
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];

            $offer_notify = Offer::create($company_id, $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getWebsite(), $latitude, $longitude);

            sendNotification($offer_notify);
        } else {
            $coordinates = getCoordinates($offer->getAddress());
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];
            Offer::update($offer->getOfferId(), $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getWebsite(), $latitude, $longitude);
        }
        PendingOffer::setStatus($offer->getId(), "Accepted");
    }
}
header("Location: ../../../view/offer/list.php?type=all");