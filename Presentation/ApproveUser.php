<?php
// Démarre une session pour gérer l'authentification des utilisateurs
session_start();

// Inclusion du fichier pour la classe Database et autoload pour charger automatiquement les bibliothèques de Composer
require "../Model/Database.php";
require '../vendor/autoload.php';
require "../Model/Email.php"; // Inclusion de la classe Email

// Vérification du rôle de l'utilisateur pour autoriser ou refuser l'accès
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 4) {
    // Si l'utilisateur n'a pas le rôle approprié, accès refusé
    header('location: AccessDenied.php');
    exit();
}

// Vérification si la requête est envoyée via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification si l'ID utilisateur est présent dans les données POST
    if (isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];

        // Création d'une instance de la base de données
        $db =(Database::getInstance());;
        // Récupération des informations utilisateur par son ID
        $user = $db->getUserById($userId);

        // Si l'utilisateur existe et qu'il est approuvé
        if ($user && $db->approveUser($userId)) {
            // Utilisation de la classe Email pour envoyer l'email
            $email = new Email();
            $subject = 'Approbation du compte';
            $body = "Cher " . $user['prenom'] . ",\n\nVotre compte a été approuvé. Vous pouvez maintenant vous connecter au système.\n\nCordialement,\nL'équipe Le Petit Stage";

            if ($email->sendEmail($user['email'], $user['prenom'] . ' ' . $user['nom'], $subject, $body)) {
                echo 'success'; // Message de succès si l'email a bien été envoyé
            } else {
                echo 'email_error'; // Indique qu'une erreur est survenue lors de l'envoi de l'email
            }
        } else {
            // Si l'utilisateur n'existe pas ou l'approbation échoue
            echo 'error';
        }
    } else {
        // Si l'ID utilisateur n'est pas fourni dans la requête POST
        echo 'error';
    }
}
?>
