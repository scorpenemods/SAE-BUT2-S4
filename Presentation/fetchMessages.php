<?php
session_start();
require "../Model/Database.php";

$senderId = $_SESSION['user_id'] ?? null;
$receiverId = $_POST['receiver_id'] ?? null;

if ($senderId && $receiverId) {
    $database = new Database();
    $messages = $database->getMessages($senderId, $receiverId);

    echo json_encode([
        'status' => 'success',
        'messages' => $messages
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User or receiver not set.']);
}
?>