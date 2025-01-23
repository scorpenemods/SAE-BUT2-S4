<?php
/*
 * getfile.php
 * Allows the user to download a file from the server.
 */

require $_SERVER['DOCUMENT_ROOT'] . '/models/Applications.php';

function downloadFile($user, $offer, $type): bool {
    error_reporting(E_ALL ^ E_DEPRECATED);
    $user = filter_var($user, FILTER_SANITIZE_NUMBER_INT);
    $offer = filter_var($offer, FILTER_SANITIZE_NUMBER_INT);
    $type = filter_var($type, FILTER_SANITIZE_STRING);

    $name = md5($user . ":" . $offer . ":" . $type);
    $file_path = './uploads/' . $name . '.pdf';

    if (file_exists($file_path)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . Applications::getUsername($user) . '_' . $type . '.pdf"');
        header('Content-Length: ' . filesize($file_path));


        readfile($file_path);
        return true;
    } else {
        header("HTTP/1.1 404 Not Found");

        echo "File not found.";
        echo "User : " . $user . ", Offer : " . $offer . ", Type : " . $type . ", name : " . $name . ".pdf";

        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user']) && isset($_POST['offer']) && isset($_POST['type'])) {
    $result = downloadFile($_POST['user'], $_POST['offer'], $_POST['type']);
    if (!$result) {
        http_response_code(400);
        echo "Error in request.";
    }
} else {
    http_response_code(400);
    echo "Bad request";
}

exit;