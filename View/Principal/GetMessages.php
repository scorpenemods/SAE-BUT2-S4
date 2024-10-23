<?php
session_start();
require "../../Model/Database.php";
require "../../Model/Person.php";

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userId = $person->getUserId();
    } else {
        echo "Session invalide.";
        exit();
    }
} else {
    echo "Utilisateur non connecté.";
    exit();
}

if (!isset($_GET['contact_id'])) {
    echo "Contact non spécifié.";
    exit();
}

$contactId = intval($_GET['contact_id']);

$database = new Database();

try {
    // Récupérer les messages entre l'utilisateur et le contact
    $stmt = $database->getConnection()->prepare("
        SELECT m.*, d.filepath AS file_path
        FROM Message m
        LEFT JOIN Document_Message dm ON m.id = dm.message_id
        LEFT JOIN Document d ON dm.document_id = d.id
        WHERE (m.sender_id = :user_id AND m.receiver_id = :contact_id)
           OR (m.sender_id = :contact_id AND m.receiver_id = :user_id)
        ORDER BY m.timestamp ASC
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':contact_id', $contactId);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marquer les messages comme lus
    $stmt = $database->getConnection()->prepare("
        UPDATE Message
        SET `read` = 1
        WHERE receiver_id = :user_id AND sender_id = :contact_id AND `read` = 0
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':contact_id', $contactId);
    $stmt->execute();

    // Afficher les messages
    foreach ($messages as $msg) {
        if ($msg['sender_id'] == $userId) {
            $messageType = 'self';
        } else {
            $messageType = 'other';
        }

        echo '<div class="message ' . $messageType . '" data-message-id="' . $msg['id'] . '">';
        if (!empty($msg['contenu'])) {
            echo '<p>' . htmlspecialchars($msg['contenu']) . '</p>';
        }
        if (!empty($msg['file_path'])) {
            $fileUrl = htmlspecialchars(str_replace("../", "/", $msg['file_path']));
            $fileName = basename($msg['file_path']);
            echo '<a href="' . $fileUrl . '" download>' . htmlspecialchars($fileName) . '</a>';
        }
        echo '<div class="timestamp-container"><span class="timestamp">' . htmlspecialchars($msg['timestamp']) . '</span></div>';
        echo '</div>';
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des messages : " . $e->getMessage());
    echo "Erreur lors de la récupération des messages.";
}
?>
