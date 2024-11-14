<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once '../../Model/Database.php';

if (isset($_POST['student_ids'], $_POST['professor_id'], $_POST['maitre_id'])) {
    $studentIds = $_POST['student_ids'];
    $professorId = $_POST['professor_id'];
    $maitreId = $_POST['maitre_id'];

    $database = Database::getInstance();

    // Call function to create the group
    $result = createGroup($studentIds, $professorId, $maitreId, $database);

    // Return appropriate JSON response
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Group created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating group.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data to create the group.']);
}

function createGroup($studentIds, $professorId, $maitreId, $database) {
    try {
        // Start a transaction
        $database->getConnection()->beginTransaction();

        // Generate a unique group name (convention)
        // For example, "Group - Student1, Student2"
        $studentNames = [];
        foreach ($studentIds as $studentId) {
            $student = $database->getUserById($studentId);
            $studentNames[] = $student['prenom'] . ' ' . $student['nom'];
        }
        $groupName = 'Group - ' . implode(', ', $studentNames);

        // Insert a new convention (group)
        $stmt = $database->getConnection()->prepare("INSERT INTO Convention (convention) VALUES (:convention)");
        $stmt->execute([':convention' => $groupName]);
        $conventionId = $database->getConnection()->lastInsertId();

        // Add users to the group
        $users = array_merge($studentIds, [$professorId, $maitreId]);
        foreach ($users as $userId) {
            $stmt = $database->getConnection()->prepare("INSERT INTO Groupe (conv_id, user_id) VALUES (:conv_id, :user_id)");
            $stmt->execute([':conv_id' => $conventionId, ':user_id' => $userId]);
        }

        // Commit the transaction
        $database->getConnection()->commit();
        return true;
    } catch (PDOException $e) {
        // Rollback the transaction in case of error
        $database->getConnection()->rollBack();
        error_log("Error creating group: " . $e->getMessage());
        return false;
    }
}
?>