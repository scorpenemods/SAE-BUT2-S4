<?php
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

// Activer l'affichage des erreurs (il nous supprimer plus tard en production)
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Définir le type de contenu de la réponse
header('Content-Type: application/json; charset=utf-8');

// Vérification de la session utilisateur
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $senderId = $person->getUserId(); // ID de l'utilisateur connecté
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Session invalide. Veuillez vous reconnecter.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Utilisateur non connecté.']);
    exit();
}

// Vérification de la méthode de requête
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $database = new Database();

    // Récupération des données du formulaire
    $receiverId = $_POST['receiver_id'] ?? null;
    $message = $_POST['message'] ?? '';
    $filePath = '';
    $fileName = '';

    // Validation de l'ID du destinataire
    if (!$receiverId) {
        echo json_encode(['status' => 'error', 'message' => 'ID du destinataire non spécifié.']);
        exit();
    }

    // Traitement du fichier si un fichier est envoyé
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = "../uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = basename($_FILES['file']['name']);
            $fileTmpPath = $_FILES['file']['tmp_name'];

            // Validation du type de fichier
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'gif', 'mp4', 'avi', 'zip', 'rar', 'csv'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                echo json_encode(['status' => 'error', 'message' => 'Type de fichier non autorisé.']);
                exit();
            }

            // Validation de la taille du fichier (max 10 MB)
            $maxFileSize = 10 * 1024 * 1024; // 10 MB
            if ($_FILES['file']['size'] > $maxFileSize) {
                echo json_encode(['status' => 'error', 'message' => 'Le fichier est trop volumineux.']);
                exit();
            }

            // Génération d'un nom de fichier unique
            $newFileName = uniqid('file_', true) . '.' . $fileExtension;
            $filePath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $filePath)) {
                // Fichier téléchargé avec succès
                if (empty($message)) {
                    $message = "Fichier envoyé: " . $fileName;
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors du téléchargement du fichier.']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors du téléchargement du fichier.']);
            exit();
        }
    }

    // Validation du contenu du message ou du fichier
    if (empty($message) && empty($filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'Le message est vide.']);
        exit();
    }

    // Envoi du message à la base de données et récupération de l'ID du message
    $messageId = $database->sendMessage($senderId, $receiverId, $message, $filePath, $fileName);
    if ($messageId) {
        // Récupérer le message inséré pour obtenir le timestamp exact
        $stmt = $database->getConnection()->prepare("
            SELECT m.*, d.filepath AS file_path
            FROM Message m
            LEFT JOIN Document_Message dm ON m.id = dm.message_id
            LEFT JOIN Document d ON dm.document_id = d.id
            WHERE m.id = :message_id
        ");


        $stmt->bindParam(':message_id', $messageId);
        $stmt->execute();
        $messageData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($messageData) {
            $response = [
                'status'     => 'success',
                'message'    => htmlspecialchars($messageData['contenu']),
                'file_path'  => $messageData['file_path'] ? htmlspecialchars(str_replace("../", "/", $messageData['file_path'])) : null,
                'timestamp'  => date('c'), // format ISO 8601
                'sender_id'  => $messageData['sender_id'],
                'message_id' => $messageData['id'],
            ];
            echo json_encode($response);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la récupération du message.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi du message.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Méthode de requête invalide.']);
}
?>