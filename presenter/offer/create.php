<?php
session_start();

require dirname(__FILE__) . "/../../models/PendingOffer.php";
require dirname(__FILE__) . "/../../models/Company.php";

if (isset($_SESSION['user']) && isset($_SESSION['company_id'])) {
    $user_id = $_SESSION['user'];
    $company_id = $_SESSION['company_id'];
} else {
    header("Location: ../offer/view/create.php");
    die();
}

if (isset($_POST['id'])) {
    $offers = PendingOffer::getByOfferId($_POST['id']);

    foreach ($offers as $offer) {
        $status = $offer->getStatus();
        if ($status == "Pending") {
            header("Location: ../../view/offer/list.php");
            die();
        }
    }
}

if (isset($_POST['title']) && isset($_POST['address']) && isset($_POST['job']) && isset($_POST['description']) && isset($_POST['duration']) && isset($_POST['salary']) && isset($_POST['education']) && isset($_POST['start-date']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_FILES['file-upload'])) {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
    } else {
        $id = 0;
    }
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
    $file = $_FILES['file-upload'];

    //Get the tags
    $selectedTags = array();
    $tags = Offer::getAllTags();
    foreach ($tags as $tag) {
        if (isset($_POST["tag_" . $tag])) {
            $selectedTags[] = $tag;
        }
    }

    //if ($file['error'] !== UPLOAD_ERR_OK) {
    //    echo "Erreur lors du téléchargement du fichier";
    //    die();
    //}

    $fileName = $file['name'];
    $fileType = $file['type'];
    $fileSize = $file['size'];
    $fileTmpName = $file['tmp_name'];

    $targetDir = "uploads/";
    $targetFile = $targetDir . $fileName;

    //if (move_uploaded_file($fileTmpName, $targetFile)) {
    //    echo "Le fichier a été téléchargé avec succès";
    //} else {
    //    echo "Erreur lors du téléchargement du fichier";
    //    die();
    //}

    //Create the offer
    $offer = pendingOffer::createPending($company_id, $title, $description, $job, $duration, $salary, $address, $education, $startDate, $selectedTags, $email, $phone, $fileName, $fileType, $fileSize, $user_id, $id);


    //If the offer is created, redirect to the list of pending offers
    if ($offer) {
        header("Location: ../../view/offer/list.php");
        die();
    }
}