<?php
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

// Set response content type to JSON
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

// Check if required parameters are set
if (!isset($_GET['contact_id']) || !isset($_GET['last_timestamp'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters: contact_id or last_timestamp.']);
    exit();
}

$contactId = intval($_GET['contact_id']);
$lastTimestamp = $_GET['last_timestamp'];

$database = Database::getInstance();

try {
    // Fetch new messages since last timestamp
    $stmt = $database->getConnection()->prepare("
        SELECT m.*, d.filepath AS file_path
        FROM Message m
        LEFT JOIN Document_Message dm ON m.id = dm.message_id
        LEFT JOIN Document d ON dm.document_id = d.id
        WHERE ((m.sender_id = :user_id AND m.receiver_id = :contact_id)
            OR (m.sender_id = :contact_id AND m.receiver_id = :user_id))
            AND m.group_id IS NULL
            AND m.timestamp > :last_timestamp
        ORDER BY m.timestamp ASC
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
    $stmt->bindParam(':last_timestamp', $lastTimestamp);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare messages for output
    foreach ($messages as &$msg) {
        $msg['contenu'] = htmlspecialchars($msg['contenu']);
        $msg['file_path'] = $msg['file_path'] ? htmlspecialchars(str_replace("../", "/", $msg['file_path'])) : null;
        $msg['timestamp'] = $msg['timestamp'];
        // Remove unnecessary fields if needed
    }
    unset($msg); // Break reference

    echo json_encode(['status' => 'success', 'messages' => $messages]);
    exit();
} catch (Exception $e) {
    error_log("Error fetching new messages: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error fetching new messages.']);
    exit();
}