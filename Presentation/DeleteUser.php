<?php
// Démarre une session pour gérer l'authentification et les données de session utilisateur
session_start();

// Inclusion de la classe Database et de l'autoload pour les bibliothèques de Composer
require "../Model/Database.php";
require '../vendor/autoload.php';
require "../Model/Email.php";

// Affiche toutes les erreurs temporairement pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification du rôle utilisateur pour restreindre l'accès
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 4) {
    // Si l'utilisateur n'a pas le rôle requis (ici 4), on bloque l'accès
    header('location: AccessDenied.php');
    exit();
}

// Vérifie si la requête a été envoyée via la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifie si l'ID de l'utilisateur à supprimer est passé dans le formulaire
    if (isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];

        // Crée une instance de la base de données
        $db = (Database::getInstance());
        // Récupère les informations de l'utilisateur à partir de l'ID
        $user = $db->getUserById($userId);

        // Si l'utilisateur existe et est supprimé avec succès
        if ($user && $db->deleteUser($userId)) {
            // Utilise la classe Email pour envoyer l'email
            $email = new Email();
            $subject = 'Suppression de votre compte';
            $body = "Cher " . $user['prenom'] . ",\n\nVotre compte a été supprimé du système.\n\nCordialement,\nL'équipe Le Petit Stage";

            // Insert log entry
            $logQuery = "INSERT INTO Logs (user_id, type, description, date) VALUES (:user_id, 'ACTION', :description, NOW())";
            $stmtLog = $db->getConnection()->prepare($logQuery);
            $stmtLog->bindParam(':user_id', $_SESSION['user_id']);
            $description = "Deleted user account: ID {$userId}";
            $stmtLog->bindParam(':description', $description);
            $stmtLog->execute();

            if ($email->sendEmail($user['email'], $user['prenom'] . ' ' . $user['nom'], $subject, $body)) {
                echo 'success'; // Email envoyé avec succès
            } else {
                echo 'email_error'; // Problème avec l'envoi de l'email
            }
        } else {
            // Si la suppression échoue ou l'utilisateur n'existe pas
            echo 'error';
        }
    } else {
        // Si l'ID de l'utilisateur n'a pas été envoyé dans le formulaire
        echo 'error';
    }
}
?>
