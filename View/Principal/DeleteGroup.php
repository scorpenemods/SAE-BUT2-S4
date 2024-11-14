<?php
session_start();
require_once '../../Model/Database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $groupId = $input['group_id'] ?? null;

    if ($groupId) {
        $database = Database::getInstance();
        $result = $database->deleteGroup($groupId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Group deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting group.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Group ID not provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>