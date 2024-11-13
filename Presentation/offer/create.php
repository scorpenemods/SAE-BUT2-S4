<?php
session_start();

require dirname(__FILE__) . "/../../Model/PendingOffer.php";
require dirname(__FILE__) . "/../../Model/Company.php";

if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
} else {
    header("Location: ../offer/create.php");
    die();
}

if (isset($_POST['id'])) {
    $offers = PendingOffer::getByOffer($_POST['id']);
    foreach ($offers as $offer) {
        $status = $offer->getStatus();
        if ($status == "Pending") {
            header("Location: ../../View/offer/list.php");
            die();
        }
    }
}

if (isset($_POST['company_id']) && isset($_POST['title']) && isset($_POST['address']) && isset($_POST['job']) && isset($_POST['description']) && isset($_POST['duration']) && isset($_POST['salary']) && isset($_POST['education']) && isset($_POST['start-date']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['website'])) {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
    } else {
        $id = 0;
    }
    $company_id = $_POST['company_id'];
    $title = $_POST['title'];
    $address = $_POST['address'];
    $job = $_POST['job'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $salary = $_POST['salary'];
    $education = $_POST['education'];
    $startDate = $_POST['start-date'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $website = $_POST['website'];

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
        header("Location: ../../View/offer/list.php");
        die();
    }
}