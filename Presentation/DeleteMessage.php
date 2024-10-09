<?php
require "../Model/Database.php";
require "../Model/Person.php";

session_start();

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $currentUserId = $person->getUserId();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Session utilisateur invalide.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Utilisateur non connecté.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'])) {
    $messageId = $_POST['message_id'];

    $database = new Database();
    $message = $database->getMessageById($messageId);

    if ($message && $message['sender_id'] == $currentUserId) {
        if ($database->deleteMessage($messageId)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression dans la base de données.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Vous n\'avez pas la permission de supprimer ce message.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID de message non fourni.']);
}
?>
