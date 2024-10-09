<?php
// Démarre une session pour gérer l'authentification et les données de session utilisateur
session_start();

// Inclusion de la classe Database et de l'autoload pour les bibliothèques de Composer
require "../Model/Database.php";
require '../vendor/autoload.php';

// Utilisation des classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $db = new Database();
        // Récupère les informations de l'utilisateur à partir de l'ID
        $user = $db->getUserById($userId);

        // Si l'utilisateur existe et est supprimé avec succès
        if ($user && $db->deleteUser($userId)) {
            // Envoi d'un email à l'utilisateur pour l'informer de la suppression de son compte
            $mail = new PHPMailer(true);

            try {
                // Configuration du serveur SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Serveur SMTP du fournisseur de messagerie
                $mail->SMTPAuth = true;
                $mail->Username = 'secretariat.lps.official@gmail.com'; // Adresse e-mail de l'expéditeur
                $mail->Password = 'xtdu vchi sldx qmyi'; // Mot de passe ou mot de passe d'application pour l'e-mail
                $mail->SMTPSecure = 'tls'; // Protocole de sécurisation (tls ou ssl)
                $mail->Port = 587; // Port SMTP (587 pour tls, 465 pour ssl)

                // Définition des destinataires
                $mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team'); // Adresse de l'expéditeur
                $mail->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']); // Adresse du destinataire

                // Contenu de l'email
                $mail->isHTML(false); // Définit si l'e-mail est en HTML ou en texte brut
                $mail->Subject = 'Suppression de votre compte'; // Sujet de l'e-mail
                $mail->Body = "Cher " . $user['prenom'] . ",\n\nVotre compte a été supprimé du système.\n\nCordialement,\nL'équipe Le Petit Stage"; // Corps de l'e-mail

                // Envoi de l'email
                $mail->send();
                echo 'success'; // Message de succès si l'e-mail a été envoyé correctement
            } catch (Exception $e) {
                // En cas d'échec de l'envoi, loguer l'erreur mais ne pas l'afficher en production
                error_log("Le message n'a pas pu être envoyé. Erreur Mailer : {$mail->ErrorInfo}");
                echo 'email_error'; // Indiquer qu'il y a eu une erreur avec l'e-mail
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
