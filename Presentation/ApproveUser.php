<?php
// Démarre une session pour gérer l'authentification des utilisateurs
session_start();

// Inclusion du fichier pour la classe Database et autoload pour charger automatiquement les bibliothèques de Composer
require "../Model/Database.php";
require '../vendor/autoload.php';

// Importation des classes PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $db = new Database();
        // Récupération des informations utilisateur par son ID
        $user = $db->getUserById($userId);

        // Si l'utilisateur existe et qu'il est approuvé
        if ($user && $db->approveUser($userId)) {
            // Envoi de l'email en utilisant PHPMailer
            require '../vendor/autoload.php'; // Chargement automatique des dépendances Composer

            $mail = new PHPMailer(true);

            try {
                // Configuration du serveur SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Serveur SMTP de votre fournisseur de messagerie
                $mail->SMTPAuth = true;
                $mail->Username = 'secretariat.lps.official@gmail.com'; // Adresse email utilisée pour l'envoi
                $mail->Password = 'xtdu vchi sldx qmyi'; // Mot de passe d'accès de l'application Gmail (à sécuriser dans le futur)
                $mail->SMTPSecure = 'tls'; // Protocole de sécurisation (tls ou ssl)
                $mail->Port = 587; // Port SMTP (587 pour tls, 465 pour ssl)

                // Destinataires
                $mail->setFrom('no-reply@seciut.com', 'Le Petit Stage Team'); // Adresse de l'expéditeur
                $mail->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']); // Adresse du destinataire

                // Contenu de l'email
                $mail->isHTML(false); // Défini à true si vous envoyez un email en HTML
                $mail->Subject = 'Approbation du compte'; // Sujet de l'email
                $mail->Body = "Cher " . $user['prenom'] . ",\n\nVotre compte a été approuvé. Vous pouvez maintenant vous connecter au système.\n\nCordialement,\nL'équipe Le Petit Stage"; // Corps de l'email

                // Envoi de l'email
                $mail->send();
                echo 'success'; // Message de succès si l'email a bien été envoyé
            } catch (Exception $e) {
                // En cas d'erreur d'envoi, consigner l'erreur dans les logs
                error_log("Le message n'a pas pu être envoyé. Erreur Mailer : {$mail->ErrorInfo}");
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
