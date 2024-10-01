<?php
session_start();

global $company_id;
if (isset($_SESSION['user'])) {
    $company_id = $_SESSION['company_id'];
}

if (isset($_POST['title']) && isset($_POST['address']) && isset($_POST['job']) && isset($_POST['description']) && isset($_POST['duration']) && isset($_POST['salary']) && isset($_POST['education']) && isset($_POST['start-date']) && isset($_POST['tags']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_FILES['file-upload']) && isset($_POST['selected-tags'])) {
    $title = $_POST['title'];
    $address = $_POST['address'];
    $job = $_POST['job'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];
    $salary = $_POST['salary'];
    $education = $_POST['education'];
    $startDate = $_POST['start-date'];
    $tags = $_POST['tags'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $file = $_FILES['file-upload'];

    $selectedTags = $_POST['selected-tags'];

    echo $selectedTags;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "Erreur lors du téléchargement du fichier";
    }

    $fileName = $file['name'];
    $fileType = $file['type'];
    $fileSize = $file['size'];
    $fileTmpName = $file['tmp_name'];

    $targetDir = "uploads/";
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($fileTmpName, $targetFile)) {
        echo "Le fichier a été téléchargé avec succès";
    } else {
        echo "Erreur lors du téléchargement du fichier";
    }

    Offer::create($company_id, $title, $description, $job, $duration, $salary, $address, true,  $education, $startDate, $tags, $email, $phone, $fileName, $fileType, $fileSize);
}