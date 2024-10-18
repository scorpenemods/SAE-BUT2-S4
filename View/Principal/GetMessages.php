<?php
session_start();
require "../../Model/Database.php";

if (isset($_GET['contact_id']) && isset($_SESSION['user'])) {
    $contactId = $_GET['contact_id'];
    $person = unserialize($_SESSION['user']);
    $userId = $person->getUserId();

    $database = new Database();

    // Récupérer les messages entre les deux utilisateurs
    $messages = $database->getMessagesBetweenUsers($userId, $contactId);

    // Fonction pour formater les dates
    require_once '../../Model/utils.php';

    // Afficher les messages
    foreach ($messages as $msg) {
        $messageClass = ($msg['sender_id'] == $userId) ? 'self' : 'other';
        echo "<div class='message $messageClass' data-message-id='" . htmlspecialchars($msg['id']) . "'>";
        echo "<p>" . htmlspecialchars($msg['contenu']) . "</p>";
        // Afficher le timestamp formaté
        echo "<div class='timestamp-container'><span class='timestamp'>" . formatTimestamp($msg['timestamp']) . "</span></div>";
        echo "</div>";
    }
}
?>