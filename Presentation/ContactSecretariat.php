<?php
global $userRole;
session_start();

require "../Model/Database.php";
require "../Model/Person.php";
require '../vendor/autoload.php';
require "../Model/Email.php";

// Vérification de l'authentification
if (!isset($_SESSION['user'])) {
    header('location: Logout.php');
    exit();
}

// Récupération de l'utilisateur connecté
$person = unserialize($_SESSION['user']);
$userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
$userEmail = $person->getEmail();
$userRole = $person->getRole();

// Vérification que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération du sujet et du message
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $messageContent = isset($_POST['message']) ? trim($_POST['message']) : '';
    $file = isset($_FILES['file']) ? $_FILES['file'] : null;

    if (empty($messageContent)) {
        echo "Le message ne peut pas être vide.";
        exit();
    }

    // Gestion de la pièce jointe
    $attachmentPath = null;
    if ($file && $file['error'] == UPLOAD_ERR_OK) {
        // Validation du fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            echo "Type de fichier non autorisé.";
            exit();
        }

        // Limite de taille du fichier (par exemple, 5 Mo)
        $maxFileSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxFileSize) {
            echo "Le fichier est trop volumineux.";
            exit();
        }

        $uploadsDir = '../uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        $filename = basename($file['name']);
        $targetFile = $uploadsDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $attachmentPath = $targetFile;
        } else {
            echo "Erreur lors du téléchargement du fichier.";
            exit();
        }
    }

    // Adresse email du secrétariat
    $secretariatEmail = 'secretariat.lps.official@gmail.com';

    // Préparation de l'email
    $email = new Email();

    // Définir l'expéditeur | a changer plus tard
    $email->getMail()->setFrom('secretariat.lps.official@gmail.com', 'Le Petit Stage');

    // Ajouter le destinataire
    $email->getMail()->addAddress($secretariatEmail, 'Secrétariat');

    // Définir le Reply-To pour que le secrétariat puisse répondre à l'utilisateur
    $email->getMail()->addReplyTo($userEmail, $userName);

    // Sujet et corps de l'email
    $emailSubject = !empty($subject) ? $subject : 'Message de ' . $userName;
    $body = "<p><strong>Message de :</strong> " . $userName . " (" . $userEmail . ")</p>";
    $body .= "<p>" . nl2br(htmlspecialchars($messageContent, ENT_QUOTES, 'UTF-8')) . "</p>";

    $email->getMail()->Subject = $emailSubject;
    $email->getMail()->Body = $body;
    $email->getMail()->isHTML(true);

    // Ajouter la pièce jointe si elle existe
    if ($attachmentPath) {
        $email->getMail()->addAttachment($attachmentPath);
    }

    try {
        $email->getMail()->send();
        $sent = true;
    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi de l'email : {$email->getMail()->ErrorInfo}");
        $sent = false;
    }

    // Suppression du fichier téléchargé après l'envoi
    if ($attachmentPath) {
        unlink($attachmentPath);
    }

    if ($sent) {
        echo "<script>alert('Votre message a été envoyé au secrétariat.'); window.location.href='Student.php?section=0';</script>";
    } else {
        echo "Erreur lors de l'envoi du message. Veuillez réessayer plus tard.";
    }
} else {
    // La page d'accueil en fonction du rôle de l'utilisateur
    if ($userRole == 1) {
        header('location: Student.php?section=1');
    } elseif ($userRole == 2) {
        header('location: Professor.php?section=5');
    } elseif ($userRole == 3) {
        header('location: MaitreStage.php?section=5');
    }
    exit();
}
