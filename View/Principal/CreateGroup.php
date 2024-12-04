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

if (isset($_POST['student_ids'], $_POST['professor_id'], $_POST['maitre_id'])) {
    $studentIds = $_POST['student_ids'];
    $professorId = $_POST['professor_id'];
    $maitreId = $_POST['maitre_id'];

    $database = Database::getInstance();
    $conn = $database->getConnection();

    // Get data from post form
    $studentIds = $_POST['student_ids'] ?? [];
    $professorId = $_POST['professor_id'] ?? null;
    $maitreId = $_POST['maitre_id'] ?? null;

    // Check if all users are with valid status
    $allUserIds = array_merge($studentIds, [$professorId, $maitreId]);
    $placeholders = implode(',', array_fill(0, count($allUserIds), '?'));
    $query = "SELECT id FROM User WHERE id IN ($placeholders) AND status_user = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute($allUserIds);
    $validUserIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($validUserIds) !== count($allUserIds)) {
        echo json_encode(['success' => false, 'message' => 'Un ou plusieurs utilisateurs ne sont pas validés.']);
        exit();
    }

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

        // Insert log entry
        $logQuery = "INSERT INTO Logs (user_id, type, description, date) VALUES (:user_id, 'ACTION', :description, NOW())";
        $stmtLog = $database->getConnection()->prepare($logQuery);
        $stmtLog->bindParam(':user_id', $_SESSION['user_id']);
        $description = "Created group: {$groupName}";
        $stmtLog->bindParam(':description', $description);
        $stmtLog->execute();

        return true;
    } catch (PDOException $e) {
        // Rollback the transaction in case of error
        $database->getConnection()->rollBack();
        error_log("Error creating group: " . $e->getMessage());
        return false;
    }
}
