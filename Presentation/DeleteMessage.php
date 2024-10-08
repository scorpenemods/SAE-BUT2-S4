<?php
// Inclusion des fichiers nécessaires pour la base de données et la gestion des utilisateurs
require "../Model/Database.php";
require "../Model/Person.php";

// Démarre la session pour accéder aux informations de l'utilisateur connecté
session_start();

// Vérifie si la requête a été envoyée via la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère l'ID du message envoyé par le formulaire
    $messageId = $_POST['message_id'];

    // Récupère l'utilisateur actuel à partir de la session (désérialisation de l'objet utilisateur)
    $person = unserialize($_SESSION['user']);
    $currentUserId = $person->getUserId(); // Récupère l'ID de l'utilisateur connecté

    // Crée une instance de la base de données pour les interactions
    $database = new Database();

    // Vérifie si l'utilisateur actuel est bien l'expéditeur du message
    $message = $database->getMessageById($messageId);
    if ($message && $message['sender_id'] == $currentUserId) {
        // Si l'utilisateur est l'expéditeur, tente de supprimer le message
        $result = $database->deleteMessage($messageId);
        if ($result) {
            // Suppression réussie
            echo 'success';
        } else {
            // Erreur lors de la suppression du message dans la base de données
            echo 'Erreur lors de la suppression dans la base de données.';
        }
    } else {
        // Si l'utilisateur n'est pas l'expéditeur, il ne peut pas supprimer le message
        echo 'Vous n\'avez pas la permission de supprimer ce message.';
    }
}
?>
