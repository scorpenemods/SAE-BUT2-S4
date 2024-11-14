<?php
session_start();
require "../../Model/Database.php";
require "../../Model/Person.php";

header('Content-Type: text/html; charset=utf-8');

// Check user session
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userId = $person->getUserId();
    } else {
        echo "Utilisateur non connecté.";
        exit();
    }
} else {
    echo "Utilisateur non connecté.";
    exit();
}

if (isset($_GET['group_id'])) {
    $groupId = $_GET['group_id'];
    $database = Database::getInstance();
    $messages = $database->getGroupMessages($groupId);

    foreach ($messages as $message) {
        $senderId = $message['sender_id'];
        $sender = $database->getUserById($senderId);
        $senderName = htmlspecialchars($sender['prenom'] . ' ' . $sender['nom']);
        $messageContent = htmlspecialchars($message['contenu']);
        $timestamp = $message['timestamp'];

        // Determine message type (self or other)
        $messageType = ($senderId == $userId) ? 'self' : 'other';

        echo '<div class="message ' . $messageType . '">';
        echo '<span class="sender-name">' . $senderName . '</span>';
        echo '<p>' . $messageContent . '</p>';
        echo '<div class="timestamp-container"><span class="timestamp">' . $timestamp . '</span></div>';
        echo '</div>';
    }
} else {
    echo "ID de groupe non spécifié.";
    exit();
}
?>