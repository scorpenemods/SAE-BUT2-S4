<?php
session_start();

require dirname(__FILE__) . "/../../models/PendingOffer.php";
require dirname(__FILE__) . "/../../models/Company.php";

$user_id = $_SESSION['user'] ?? false;
if (!$user_id) {
    header("Location: ../offer/view/create.php");
    die();
}

$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

if (isset($id)) {
    $offers = PendingOffer::getByOffer($id);
    foreach ($offers as $offer) {
        $status = $offer->getStatus();
        if ($status == "Pending") {
            header("Location: ../../view/offer/list.php");
            die();
        }
    }
}

error_reporting(E_ALL ^ E_DEPRECATED);
if (isset($_POST['company_id']) && isset($_POST['title']) && isset($_POST['address']) && isset($_POST['job']) && isset($_POST['description']) && isset($_POST['duration']) && isset($_POST['salary']) && isset($_POST['education']) && isset($_POST['start-date']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['website'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $company_id = filter_input(INPUT_POST, 'company_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $job = filter_input(INPUT_POST, 'job', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT);
    $salary = filter_input(INPUT_POST, 'salary', FILTER_SANITIZE_NUMBER_FLOAT);
    $education = filter_input(INPUT_POST, 'education', FILTER_SANITIZE_STRING);
    $startDate = filter_input(INPUT_POST, 'start-date', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);

    //Get the tags
    $selectedTags = array();
    $tags = Offer::getAllTags();
    foreach ($tags as $tag) {
        if (isset($_POST["tag_" . $tag])) {
            $selectedTags[] = $tag;
        }
    }

    //Create the offer
    $offer = pendingOffer::createPending($company_id, $title, $description, $job, $duration, $salary, $address, $education, $startDate, $selectedTags, $email, $phone, $website, $user_id, $id);

    //If the offer is created, redirect to the list of pending offers
    if ($offer) {
        header("Location: ../../view/offer/list.php");
        die();
    }
}
