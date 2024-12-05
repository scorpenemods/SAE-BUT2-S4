<?php
session_start();
require_once '../../Model/Database.php';

if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour télécharger ce fichier.";
    exit;
}

if (isset($_GET['file'])) {
    // Récupération du chemin à partir de la requête GET
    $filePath = $_GET['file'];

    // Ajouter dynamiquement ../ pour le chemin réel
    $realPath = '../' . $filePath;

    // Vérifiez si le fichier existe et appartient à l'utilisateur
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

    if ($validFile && file_exists($realPath)) { // Vérification avec ../
        // Empêcher tout contenu avant l'envoi
        ob_clean();
        flush();

        // Envoyer les en-têtes pour le téléchargement
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($realPath));

        // Envoyer le fichier
        readfile($realPath);
        exit;
    } else {
        echo "Fichier non trouvé ou accès non autorisé.";
    }
} else {
    echo "Paramètre de fichier manquant.";
}
