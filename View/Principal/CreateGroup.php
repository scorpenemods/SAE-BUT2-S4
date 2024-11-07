<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once '../../Model/Database.php';

if (isset($_POST['student_id'], $_POST['professor_id'], $_POST['maitre_id'])) {
    $studentId = $_POST['student_id'];
    $professorId = $_POST['professor_id'];
    $maitreId = $_POST['maitre_id'];
    $conventionId = $_POST['convention_id'] ?? null;

    $database = (Database::getInstance());

    // Appel de la fonction pour créer le groupe
    $result = createGroup($studentId, $professorId, $maitreId, $conventionId, $database);

    // Retourner une réponse JSON appropriée
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Le groupe a été créé avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du groupe.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données manquantes pour créer le groupe.']);
}

function createGroup($studentId, $professorId, $maitreId, $conventionId, $database) {
    try {
        // Démarrer une transaction
        $database->getConnection()->beginTransaction();

        // Créer une nouvelle convention si aucune n'est fournie
        if (!$conventionId) {
            $stmt = $database->getConnection()->prepare("INSERT INTO Convention (convention) VALUES (:convention)");
            $stmt->execute([':convention' => 'Nouvelle Convention de Groupe']);
            $conventionId = $database->getConnection()->lastInsertId();
        }

        // Ajouter les utilisateurs au groupe
        $users = [$studentId, $professorId, $maitreId];
        foreach ($users as $userId) {
            $stmt = $database->getConnection()->prepare("INSERT INTO Groupe (conv_id, user_id) VALUES (:conv_id, :user_id)");
            $stmt->execute([':conv_id' => $conventionId, ':user_id' => $userId]);
        }

        // Valider la transaction
        $database->getConnection()->commit();
        return true;
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $database->getConnection()->rollBack();
        return false;
    }
}
?>