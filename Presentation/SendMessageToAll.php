<?php
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

$person = unserialize($_SESSION['user']);
$userRole = $person->getRole();

//que 4 et 5 (secrétaires) sont autorisés
if (!in_array($userRole, [4, 5])) {
    header('location: AccessDenied.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération du message et du fichier
    $messageContent = isset($_POST['message']) ? $_POST['message'] : '';
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

        // Limite de la taille (5 Mo)
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

    // Récupération de tous les utilisateurs validés
    $db = Database::getInstance();
    $validUsers = $db->getAllValidUsers(); // methode dans Database.php

    // Envoi de l'email à chaque utilisateur
    $email = new Email();
    $subject = 'Message du secretariat';

    foreach ($validUsers as $user) {
        $toEmail = $user['email'];
        $toName = $user['prenom'] . ' ' . $user['nom'];
        $body = "Cher " . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . ",<br><br>" . nl2br(htmlspecialchars($messageContent, ENT_QUOTES, 'UTF-8')) . "<br><br>Cordialement,<br>Le secrétariat";

        // Préparation de l'email
        $email->getMail()->clearAddresses();
        $email->getMail()->clearAttachments();
        $email->getMail()->addAddress($toEmail, $toName);
        $email->getMail()->Subject = $subject;
        $email->getMail()->Body = $body;
        $email->getMail()->isHTML(true);

        // Ajout de la pièce jointe si nécessaire
        if ($attachmentPath) {
            $email->getMail()->addAttachment($attachmentPath);
        }

        // Envoi de l'email
        try {
            $email->getMail()->send();
        } catch (Exception $e) {
            error_log("Échec de l'envoi à $toEmail : {$email->getMail()->ErrorInfo}");
        }
    }

    // Suppression du fichier téléchargé après l'envoi
    if ($attachmentPath) {
        unlink($attachmentPath);
    }

    // Redirection ou message de succès
    echo "<script>alert('Le message a été envoyé à tous les utilisateurs.'); window.location.href='Secretariat.php?section=5';</script>";
} else {
    header('location: Secretariat.php?section=5');
    exit();
}