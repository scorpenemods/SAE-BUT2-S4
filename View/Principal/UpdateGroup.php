<?php
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

$userId = $person->getUserId();
$userRole = $person->getRole();

// Проверка роли пользователя (например, роль 5 для секретариата)
if ($userRole != 5) { // Измените на соответствующую роль
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

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Groupe mis à jour avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du groupe.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode de requête invalide.']);
    exit();
}
