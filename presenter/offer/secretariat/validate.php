<?php
session_start();

require dirname(__FILE__) . '/../../../models/PendingOffer.php';
require dirname(__FILE__) . '/../../../models/Company.php';
require dirname(__FILE__) . '/../../../models/Database.php';
require dirname(__FILE__) . '/../../../presenter/offer/notify.php';

function get_coordinates($address) {
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
        $status_code = intval(substr($http_response_header[0], 9, 3));
        echo "Status Code: $status_code\n";

        if ($status_code === 200) {
            $data = json_decode($response, true);
            if ($data) {
                return [floatval($data[0]['lat']), floatval($data[0]['lon'])];
            } else {
                echo "Aucune donnée trouvée pour cette adresse.\n";
                return null;
            }
        } else {
            echo "Erreur HTTP: $status_code\n";
            echo "Réponse: $response\n";
            return null;
        }
    } catch (Exception $e) {
        echo "Erreur de requête: " . $e->getMessage() . "\n";
        return null;
    }
}

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::getByOfferId($_POST['id']);
    if ($offer->getStatus() == "Pending") {
        if ($offer->getOfferId() == 0) {
            $company_id = $offer->getCompanyId();
            $coordinates = get_coordinates($offer->getAddress());
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];
            $offer_notify = Offer::create($company_id, $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getWebsite(), $latitude, $longitude);
            sendNotification($offer_notify);
            error_log("apres lappel de sendNotification");
        } else {
            $coordinates = get_coordinates($offer->getAddress());
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];
            Offer::update($offer->getOfferId(), $offer->getTitle(), $offer->getDescription(), $offer->getJob(), $offer->getDuration(), $offer->getSalary(), $offer->getAddress(), $offer->getStudyLevel(), $offer->getBeginDate(), $offer->getTags(), $offer->getEmail(), $offer->getPhone(), $offer->getWebsite(), $latitude, $longitude);
        }
        PendingOffer::setStatus($offer->getId(), "Accepted");
    }
}
header("Location: ../../../view/offer/list.php?type=all");