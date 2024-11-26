<?php
session_start();
require "../../Model/Database.php";
require "../../Model/Person.php";

header('Content-Type: text/html; charset=utf-8');

// Check user session
if (!isset($_SESSION['user'])) {
    echo "User not logged in.";
    exit();
}

$person = unserialize($_SESSION['user']);
if (!$person instanceof Person) {
    echo "Invalid session.";
    exit();
}

$userId = $person->getUserId();

if (isset($_GET['group_id'])) {
    $groupId = $_GET['group_id'];
    $database = Database::getInstance();
    $messages = $database->getGroupMessages($groupId);

    foreach ($messages as $message) {
        $senderId = $message['sender_id'];
        $senderName = htmlspecialchars($message['prenom'] . ' ' . $message['nom']);
        $messageContent = htmlspecialchars($message['contenu']);
        $timestamp = $message['timestamp'];
        $filePath = $message['filepath'] ? htmlspecialchars(str_replace("../", "/", $message['filepath'])) : null;

        // Determine message type (self or other)
        $messageType = ($senderId == $userId) ? 'self' : 'other';

        echo '<div class="message ' . $messageType . '" data-message-id="' . htmlspecialchars($message['id']) . '" data-message-type="group">';
        echo '<span class="sender-name">' . $senderName . '</span>';
        if (!empty($messageContent)) {
            echo '<p>' . $messageContent . '</p>';
        }
        if ($filePath) {
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $imageExtensions)) {
                echo '<img src="' . $filePath . '" alt="Image" style="max-width: 200px; max-height: 200px;">';
            } else {
                $fileName = basename($filePath);
                echo '<a href="' . $filePath . '" download>' . htmlspecialchars($fileName) . '</a>';
            }
        }
        echo '<div class="timestamp-container"><span class="timestamp">' . htmlspecialchars($timestamp) . '</span></div>';
        echo '</div>';
    }
} else {
    echo "Group ID not specified.";
    exit();
}
?>