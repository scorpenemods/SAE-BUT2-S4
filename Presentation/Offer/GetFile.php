<?php
// File: GetFile.php
// Get a file from the server

use Model\Application;require '../../Models/Application.php';

function download_file($user, $offer, $type) {
    error_reporting(E_ALL ^ E_DEPRECATED);
    $user = filter_var($user, FILTER_SANITIZE_NUMBER_INT);
    $offer = filter_var($offer, FILTER_SANITIZE_NUMBER_INT);
    $type = filter_var($type, FILTER_SANITIZE_STRING);

    $name = md5($user . ":" . $offer . ":" . $type);
    $filePath = './uploads/' . $name . '.pdf';

    if (file_exists($filePath)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . Application::get_username($user) . '_' . $type . '.pdf"');
        header('Content-Length: ' . filesize($filePath));


        readfile($filePath);
        return true;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "File not found.";
        echo "User : " . $user . ", Offer : " . $offer . ", Type : " . $type . ", name : " . $name . ".pdf";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user']) && isset($_POST['Offer']) && isset($_POST['type'])) {
    $result = download_file($_POST['user'], $_POST['Offer'], $_POST['type']);
    if (!$result) {
        http_response_code(400);
        echo "Error in request.";
    }
} else {
    http_response_code(400);
    echo "Bad request";
}

exit;