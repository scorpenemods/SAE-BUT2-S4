<?php
/*
 * Ce fichier récupère et affiche tous les messages entre l'utilisateur connecté et un contact spécifique.
 * Il vérifie la session, récupère les messages depuis la base de données,
 * marque les messages comme lus, et affiche le contenu, les fichiers joints et les horodatages.
 */
session_start();
require "../../Model/Database.php";
require "../../Model/Person.php";

header('Content-Type: text/html; charset=utf-8');

// Check if user is logged in
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userId = $person->getId();
    } else {
        echo "Invalid session.";
        exit();
    }
} else {
    echo "User not logged in.";
    exit();
}

// Check if contact_id is set
if (!isset($_GET['contact_id'])) {
    echo "Contact not specified.";
    exit();
}

$contactId = intval($_GET['contact_id']);

$database = Database::getInstance();

try {
    // Fetch messages between the user and the contact
    $stmt = $database->getConnection()->prepare("
        SELECT m.*, d.filepath
        FROM Message m
        LEFT JOIN Document_Message dm ON m.id = dm.message_id
        LEFT JOIN Document d ON dm.document_id = d.id
        WHERE (m.sender_id = :user_id AND m.receiver_id = :contact_id)
           OR (m.sender_id = :contact_id AND m.receiver_id = :user_id)
        ORDER BY m.timestamp ASC
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark messages as read
    $stmt = $database->getConnection()->prepare("
        UPDATE Message
        SET `read` = 1
        WHERE receiver_id = :user_id AND sender_id = :contact_id AND `read` = 0
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
    $stmt->execute();

    // Display messages
    foreach ($messages as $msg) {
        if ($msg['sender_id'] == $userId) {
            $messageType = 'self';
        } else {
            $messageType = 'other';
        }

        echo '<div class="message ' . $messageType . '" data-message-id="' . htmlspecialchars($msg['id']) . '" data-message-type="private">';
        if (!empty($msg['contenu'])) {
            echo '<p>' . htmlspecialchars($msg['contenu']) . '</p>';
        }
        if (!empty($msg['filepath'])) {
            $filePath = htmlspecialchars(str_replace("../", "/", $msg['filepath']));
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $imageExtensions)) {
                echo '<img src="' . $filePath . '" alt="Image" style="max-width: 200px; max-height: 200px;">';
            } else {
                $fileName = basename($filePath);
                echo '<a href="' . $filePath . '" download>' . htmlspecialchars($fileName) . '</a>';
            }
        }
        echo '<div class="timestamp-container"><span class="timestamp">' . htmlspecialchars($msg['timestamp']) . '</span></div>';
        echo '</div>';
    }
} catch (Exception $e) {
    error_log("Error fetching messages: " . $e->getMessage());
    echo "Error fetching messages.";
}