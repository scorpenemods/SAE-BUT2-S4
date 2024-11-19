<?php
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
        $lastTimestamp = $_GET['last_timestamp'];

        $database = Database::getInstance();

        // Fetch new messages since last timestamp
        $messages = $database->getGroupMessagesSince($groupId, $lastTimestamp);

        // Prepare messages for output
        foreach ($messages as &$message) {
            $message['sender_name'] = htmlspecialchars($message['prenom'] . ' ' . $message['nom']);
            $message['contenu'] = htmlspecialchars($message['contenu']);
            $message['filepath'] = $message['filepath'] ? htmlspecialchars(str_replace("../", "/", $message['filepath'])) : null;
        }
        unset($message);

        echo json_encode(['status' => 'success', 'messages' => $messages]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing group_id or last_timestamp']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}