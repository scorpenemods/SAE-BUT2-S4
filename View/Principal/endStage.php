<?php
session_start();
require_once "../../Model/Database.php";
require_once "../../Model/Person.php";

header('Content-Type: application/json; charset=utf-8');

// Проверка сессии пользователя
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

$person = unserialize($_SESSION['user']);
if (!$person instanceof Person) {
    echo json_encode(['success' => false, 'message' => 'Session invalide.']);
    exit();
}

$userId = $person->getId();
$userRole = $person->getRole();

// Check user's role
if ($userRole != 4 && $userRole != 5) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $groupId = $input['group_id'] ?? null;

    if ($groupId) {
        $database = Database::getInstance();
        $database->setEndStage($groupId);
    } else {
        echo json_encode(['success' => false, 'message' => 'Group ID not provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
