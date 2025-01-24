<?php
/*
 * Obtient les membres d'un groupe spécifié.
 * Classe les membres en étudiant, professeur et maître de stage.
 * Retourne les données au format JSON.
 */
session_start();
require_once '../../Model/Database.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['group_id'])) {
    $groupId = $_GET['group_id'];

    $database = Database::getInstance();
    $groupMembers = $database->getGroupMembers($groupId);

    if ($groupMembers !== false) {
        // Assuming the group always has one student, one professor, and one maitre de stage
        $members = [
            'student_id' => null,
            'professor_id' => null,
            'maitre_id' => null
        ];

        foreach ($groupMembers as $userId) {
            $user = $database->getUserById($userId);
            if ($user['role'] == 1) {
                $members['student_id'] = $userId;
            } elseif ($user['role'] == 2) {
                $members['professor_id'] = $userId;
            } elseif ($user['role'] == 3) {
                $members['maitre_id'] = $userId;
            }
        }

        echo json_encode(['success' => true, 'members' => $members]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error fetching group details.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Group ID not provided.']);
}
?>