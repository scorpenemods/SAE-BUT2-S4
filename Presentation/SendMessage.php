<?php
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

date_default_timezone_set('Europe/Paris');

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $senderId = $person->getUserId(); // Получаем ID пользователя для отправки сообщений
    }
} else {
    header("Location: Logout.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $database = new Database();

    // Retrieve sender information
    if (!isset($_SESSION['user'])) {
        echo json_encode(['status' => 'error', 'message' => 'Utilisateur non connecté.']);
        exit();
    }
    $person = unserialize($_SESSION['user']);
    if (!$person instanceof Person) {
        echo json_encode(['status' => 'error', 'message' => 'Session invalide. Veuillez vous reconnecter.']);
        exit();
    }
    $senderId = $person->getUserId();

    $receiverId = $_POST['receiver_id'] ?? null;
    $message = $_POST['message'] ?? '';
    $filePath = '';
    $fileName = '';

    // Validate receiver ID
    if (!$receiverId) {
        echo json_encode(['status' => 'error', 'message' => 'ID du destinataire non spécifié.']);
        exit();
    }

    // Initialize an array to collect error messages
    $errors = [];

    // Handle file upload if a file is provided
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = "../uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = basename($_FILES['file']['name']);
            $fileTmpPath = $_FILES['file']['tmp_name'];

            // Validate file type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'gif', 'mp4', 'avi'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = 'Type de fichier non autorisé.';
            }

            // Validate file size (e.g., max 10 MB)
            $maxFileSize = 10 * 1024 * 1024; // 10 MB
            if ($_FILES['file']['size'] > $maxFileSize) {
                $errors[] = 'Le fichier est trop volumineux.';
            }

            if (empty($errors)) {
                // Generate a unique file name to prevent overwriting
                $newFileName = uniqid('file_', true) . '.' . $fileExtension;
                $filePath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $filePath)) {
                    // File uploaded successfully
                    // If no message is provided, set a default message
                    if (empty($message)) {
                        $message = "Fichier envoyé: " . $fileName;
                    }
                } else {
                    $errors[] = 'Erreur lors du téléchargement du fichier.';
                }
            }
        } else {
            $errors[] = 'Erreur lors du téléchargement du fichier.';
        }
    }

    // If there are errors, return them
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
        exit();
    }

    // Validate message content or file
    if (empty($message) && empty($filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'Le message est vide.']);
        exit();
    }

    // Send the message to the database
    if ($database->sendMessage($senderId, $receiverId, $message, $filePath)) {
        // Prepare the response data
        $response = [
            'status'     => 'success',
            'message'    => htmlspecialchars($message),
            'file_path'  => $filePath ? htmlspecialchars(str_replace("../", "/", $filePath)) : null,
            'file_name'  => htmlspecialchars($fileName),
            'timestamp'  => date("Y-m-d H:i"),
            'sender_id'  => $senderId,
            'message_id' => $database->getLastMessageId(),
        ]; // str_replace() adjusts the file path to be accessible from the web root.
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi du message.']);
    }
}
?>
