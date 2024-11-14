<?php
// GetNewGroupMessages.php

session_start();
require "../Model/Database.php";
require "../Model/Person.php";

header('Content-Type: application/json; charset=utf-8');

// Check user session
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$person = unserialize($_SESSION['user']);
if (!$person instanceof Person) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session. Please log in again.']);
    exit();
}

$userId = $person->getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['group_id'], $_GET['last_timestamp'])) {
        $groupId = $_GET['group_id'];
        $lastTimestamp = $_GET['last_timestamp']; // Should be in 'Y-m-d H:i:s' format

        $database = Database::getInstance();

        $timeout = 30; // seconds
        $sleepTime = 1; // seconds
        $startTime = time();

        while (true) {
            // Fetch new messages since last timestamp
            $messages = $database->getGroupMessagesSince($groupId, $lastTimestamp);

            if (!empty($messages)) {
                // Add sender_name to each message
                foreach ($messages as &$message) {
                    $message['sender_name'] = htmlspecialchars($message['prenom'] . ' ' . $message['nom']);
                    $message['contenu'] = htmlspecialchars($message['contenu']);
                    // Adjust file path
                    if (!empty($message['filepath'])) {
                        $message['filepath'] = htmlspecialchars(str_replace("../", "/", $message['filepath']));
                    }
                }
                unset($message); // Unset reference

                // Return new messages
                echo json_encode(['status' => 'success', 'messages' => $messages]);
                exit();
            }

            if ((time() - $startTime) > $timeout) {
                // Timeout reached, return no new messages
                echo json_encode(['status' => 'timeout', 'messages' => []]);
                exit();
            }

            // Sleep before checking again
            sleep($sleepTime);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing group_id or last_timestamp']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>