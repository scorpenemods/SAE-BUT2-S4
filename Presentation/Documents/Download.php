<?php
session_start();
require_once dirname(__FILE__) . '/../Model/Offer.php';
$db = Database::getInstance()->getConnection();

if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour télécharger ce fichier.";
    exit;
}

if (isset($_GET['file'])) {
    // Getting path from GET request
    $filePath = $_GET['file'];

    // Dynamically add ../ for actual path
    $realPath = '../' . $filePath;

    // Check if the file exists and is owned by the user
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    $files = $db->getFiles($userId);

    $validFile = false;
    foreach ($files as $file) {
        if ($file['path'] === $filePath) {
            $validFile = true;
            break;
        }
    }

    if ($validFile && file_exists($realPath)) { // Verification with ../
        // Block all content before sending
        ob_clean();
        flush();

        // Send headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($realPath));

        // send the file
        readfile($realPath);
        exit;
    } else {
        echo "Fichier non trouvé ou accès non autorisé.";
    }
} else {
    echo "Paramètre de fichier manquant.";
}
