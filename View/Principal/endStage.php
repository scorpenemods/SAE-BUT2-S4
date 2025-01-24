<?php
/*
 * Ce fichier gère la fin de stage pour un groupe.
 * Il vérifie la session utilisateur et les permissions,
 * reçoit une requête POST avec l'ID du groupe,
 * et met à jour le statut de fin de stage dans la base de données.
 * Répond en JSON avec le succès ou l'échec de l'opération.
 */
session_start();
require_once "../../Model/Database.php";
require_once "../../Model/Person.php";

header('Content-Type: application/json; charset=utf-8');

// Check user's session
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
if ($userRole != 2 && $userRole != 3 && $userRole != 4 && $userRole != 5) {
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
