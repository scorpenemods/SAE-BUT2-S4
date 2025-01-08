<?php
// File: Notify.php
// Notify a pending Offer
session_start();

require dirname(__FILE__) . '/../../../Model/PendingOffer.php';
require dirname(__FILE__) . '/../../../Model/Company.php';
require dirname(__FILE__) . '/../../../Presentation/Offer/Notify.php';

function get_coordinates($address): ?array
{
    $base_url = "https://nominatim.openstreetmap.org/search";
    $params = [
        'q' => $address,
        'format' => 'json',
        'limit' => 1
    ];
    $options = [
        'http' => [
            'header' => "User-Agent: YourApp/1.0 (your@email.com)\r\n",
            'method' => 'GET'
        ]
    ];

    $context = stream_context_create($options);
    $url = $base_url . '?' . http_build_query($params);

    try {
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            echo "Failed to get contents from URL\n";
            return null;
        }

        $statusCode = $http_response_header[0] ?? '';
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusCode, $matches);
        $statusCode = $matches[1] ?? 0;

        echo "Status Code: $statusCode\n";

        if ($statusCode == 200) {
            $data = json_decode($response, true);
            if (!empty($data)) {
                return [floatval($data[0]['lat']), floatval($data[0]['lon'])];
            } else {
                echo "No data found for this address.\n";
                return null;
            }
        } else {
            echo "HTTP Error: $statusCode\n";
            echo "Response: $response\n";
            return null;
        }
    } catch (Exception $e) {
        echo "Request Error: " . $e->getMessage() . "\n";
        return null;
    }
}

echo get_coordinates("New York, États-Unis d'Amérique");

if (isset($_SESSION['secretariat']) && isset($_POST['id']) && isset($_SERVER["HTTP_REFERER"])) {
    $offer = PendingOffer::get_by_offer_id($_POST['id']);
    if ($offer->get_status() == "Pending") {
        if ($offer->get_offer_id() == 0) {
            $company_id = $offer->get_company_id();
            $coordinates = get_coordinates($offer->get_address());
            echo $coordinates;
            echo $offer->get_address();
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];
            $offer_notify = Offer::create($company_id, $offer->get_title(), $offer->get_description(), $offer->get_job(), $offer->get_duration(), $offer->get_salary(), $offer->get_address(), $offer->get_study_level(), $offer->get_begin_date(), $offer->get_tags(), $offer->get_email(), $offer->get_phone(), $offer->get_website(), $latitude, $longitude);
            send_notification($offer_notify);
            error_log("apres l'appel de sendNotification");
        } else {
            $coordinates = get_coordinates($offer->get_address());
            $latitude = $coordinates[0];
            $longitude = $coordinates[1];
            Offer::update($offer->get_offer_id(), $offer->get_title(), $offer->get_description(), $offer->get_job(), $offer->get_duration(), $offer->get_salary(), $offer->get_address(), $offer->get_study_level(), $offer->get_begin_date(), $offer->get_tags(), $offer->get_email(), $offer->get_phone(), $offer->get_website(), $latitude, $longitude);
        }
        PendingOffer::set_status($offer->get_id(), "Accepted");
    }
}
//header("Location: ../../../View/Offer/List.php?type=all");