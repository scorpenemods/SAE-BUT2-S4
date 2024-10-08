<?php
require "../Model/Database.php";
require "../Model/Person.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageId = $_POST['message_id'];
    $person = unserialize($_SESSION['user']);
    $currentUserId = $person->getUserId();

    $database = new Database();

    // Check if the current user is the sender of the message
    $message = $database->getMessageById($messageId);
    if ($message && $message['sender_id'] == $currentUserId) {
        $result = $database->deleteMessage($messageId);
        if ($result) {
            echo 'success';
        } else {
            echo 'Erreur lors de la suppression dans la base de données.';
        }
    } else {
        echo 'Vous n\'avez pas la permission de supprimer ce message.';
    }
}
?>