<?php
/*
 * Supprime un groupe spécifique après vérification des permissions de l'utilisateur.
 * Traite les requêtes POST contenant l'ID du groupe à supprimer.
 * Met à jour la base de données en supprimant le groupe et enregistre l'action dans les logs.
 * Retourne une réponse JSON indiquant le succès ou l'échec de l'opération.
 */
session_start();
require "../../Model/Database.php";
require "../../Model/Person.php";

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
    // Получение данных из JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['group_id']) || !isset($input['student_ids']) || !isset($input['professor_id']) || !isset($input['maitre_id'])) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
        exit();
    }

    $groupId = intval($input['group_id']);
    $studentIds = array_map('intval', $input['student_ids']);
    $professorId = intval($input['professor_id']);
    $maitreId = intval($input['maitre_id']);

    // Объединение всех ID участников группы
    $memberIds = array_merge($studentIds, [$professorId, $maitreId]);

    // Создание экземпляра базы данных
    $database = Database::getInstance();

    // Обновление участников группы
    $result = $database->updateGroupMembers($groupId, $memberIds);

    // Insert log entry
    $logQuery = "INSERT INTO Logs (user_id, type, description, date) VALUES (:user_id, 'ACTION', :description, NOW())";
    $stmtLog = $database->getConnection()->prepare($logQuery);
    $stmtLog->bindParam(':user_id', $_SESSION['user_id']);
    $description = "Updated group: ID {$groupId}";
    $stmtLog->bindParam(':description', $description);
    $stmtLog->execute();

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Groupe mis à jour avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du groupe.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode de requête invalide.']);
    exit();
}
