<?php
// File: Create.php
// Create a pending Offer
session_start();

require_once dirname(__FILE__) . "/../../Model/PendingOffer.php";
require_once dirname(__FILE__) . "/../../Model/Company.php";

$userId = $_SESSION['user_id'] ?? false;
if (!$userId) {
    header("Location: ../Offer/View/Create.php");
    die();
}

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

if (isset($id)) {
    $offers = PendingOffer::get_by_offer($id) ?? [];
    foreach ($offers as $offer) {
        $status = $offer->get_status();
        if ($status == "Pending") {
            header("Location: ../../View/Offer/List.php");
            die();
        }
    }
}

error_reporting(E_ALL ^ E_DEPRECATED);
if (isset($_POST['company_id']) && isset($_POST['title']) && isset($_POST['address']) && isset($_POST['job']) && isset($_POST['description']) && isset($_POST['duration']) && isset($_POST['salary']) && isset($_POST['education']) && isset($_POST['start-date']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['latitude']) && isset($_POST['longitude'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT) ?? false;
    $companyId = filter_input(INPUT_POST, 'company_id', FILTER_SANITIZE_NUMBER_INT) ?? false;
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING) ?? false;
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) ?? false;
    $job = filter_input(INPUT_POST, 'job', FILTER_SANITIZE_STRING) ?? false;
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING) ?? false;
    $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT) ?? false;
    $salary = filter_input(INPUT_POST, 'salary', FILTER_SANITIZE_NUMBER_FLOAT) ?? false;
    $education = filter_input(INPUT_POST, 'education', FILTER_SANITIZE_STRING) ?? false;
    $startDate = filter_input(INPUT_POST, 'start-date', FILTER_SANITIZE_STRING) ?? false;
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? false;
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING) ?? false;
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL) ?? false;
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT) ?? false;
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT) ?? false;

    if (!$title || !$address || !$job || !$description || !$duration || !$salary || !$education || !$startDate || !$email || !$phone || !$website || !$latitude || !$longitude) {
        header("Location: ../../View/Offer/Create.php?failure");
        die();
    }
    //Get the tags
    $selectedTags = array();
    $tags = Offer::get_all_tags();
    foreach ($tags as $tag) {
        if (isset($_POST["tag" . $tag])) {
            $selectedTags[] = $tag;
        }
    }

    //Create the Offer
    $offer = pendingOffer::create_pending($companyId, $title, $description, $job, $duration, $salary, $address, $education, $startDate, $selectedTags, $email, $phone, $website, $userId, $id, floatval($latitude), floatval($longitude));
    //If the Offer is created, redirect to the list of pending offers
    if ($offer) {
        header("Location: ../../View/Offer/List.php");
        die();
    }
}
