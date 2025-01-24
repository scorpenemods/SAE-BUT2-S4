<?php
/*
 * Vérifie la session et les permissions.
 * Supprime un groupe basé sur l'ID fourni.
 * Enregistre l'action dans les logs et retourne une réponse JSON.
 */


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
        $result = $database->deleteGroup($groupId);

        // Insert log entry
        $logQuery = "INSERT INTO Logs (user_id, type, description, date) VALUES (:user_id, 'ACTION', :description, NOW())";
        $stmtLog = $database->getConnection()->prepare($logQuery);
        $stmtLog->bindParam(':user_id', $_SESSION['user_id']);
        $description = "Deleted group: ID {$groupId}";
        $stmtLog->bindParam(':description', $description);
        $stmtLog->execute();

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
