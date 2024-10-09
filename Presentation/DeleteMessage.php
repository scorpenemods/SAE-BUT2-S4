<?php
// Inclusion des fichiers nécessaires pour la base de données et la gestion des utilisateurs
require "../Model/Database.php";
require "../Model/Person.php";

// Démarre la session pour accéder aux informations de l'utilisateur connecté
session_start();

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    // Vérifie si l'objet déserialisé est une instance de la classe Person
    if ($person instanceof Person) {
        // Sécurise et affiche le prénom et le nom de la personne connectée
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
    }
} else {
    // Si aucune session d'utilisateur n'est trouvée, redirige vers la page de déconnexion
    header("Location: Logout.php");
    exit();
}

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
