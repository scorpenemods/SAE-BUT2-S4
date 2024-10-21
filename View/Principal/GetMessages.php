<?php
session_start();
require "../../Model/Database.php";
require "../../Model/Person.php";

date_default_timezone_set('Europe/Paris');

if (isset($_GET['contact_id'], $_SESSION['user'])) {
    $contactId = $_GET['contact_id'];
    $person = unserialize($_SESSION['user']);
    $userId = $person->getUserId();

    $database = new Database();

    // Récupérer les messages entre deux utilisateurs, ordonnés par date croissante
    $messages = $database->getMessagesBetweenUsers($userId, $contactId);

    require_once "../../Model/utils.php";

    foreach ($messages as $msg) {
        $messageClass = ($msg['sender_id'] == $userId) ? 'self' : 'other';
        echo "<div class='message $messageClass' data-message-id='" . htmlspecialchars($msg['id']) . "'>";
        echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>";

        if ($msg['filepath']) {
            $fileUrl = htmlspecialchars(str_replace("../", "/", $msg['filepath']));
            // Extraire le nom du fichier à partir du chemin
            $fileName = basename($msg['filepath']);
            echo "<a href='" . $fileUrl . "' download>" . htmlspecialchars($fileName) . "</a>";
        }


        echo "<div class='timestamp-container'><span class='timestamp'>" . formatTimestamp($msg['timestamp']) . "</span></div>";
        echo "</div>";
    }
} else {
    echo "Erreur : Paramètres invalides.";
}
?>