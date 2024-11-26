<?php
session_start();
require "../Model/Database.php";
require "../Model/Person.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userId = $person->getId();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Session invalide.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Utilisateur non connecté.']);
    exit();
}

// Récupérer le temps de la dernière vérification
$lastCheckTime = $_SESSION['last_check_time'] ?? null;
$_SESSION['last_check_time'] = date('Y-m-d H:i:s'); // Mettre à jour le temps de la dernière vérification

$database = Database::getInstance();

try {
    // Si c'est la première vérification, on récupère tous les messages non lus
    if ($lastCheckTime === null) {
        $stmt = $database->getConnection()->prepare("
            SELECT sender_id, COUNT(*) as message_count
            FROM Message
            WHERE receiver_id = :user_id AND `read` = 0
            GROUP BY sender_id
        ");
        $stmt->bindParam(':user_id', $userId);
    } else {
        $stmt = $database->getConnection()->prepare("
            SELECT sender_id, COUNT(*) as message_count
            FROM Message
            WHERE receiver_id = :user_id AND timestamp > :last_check_time AND `read` = 0
            GROUP BY sender_id
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':last_check_time', $lastCheckTime);
    }

    $stmt->execute();
    $newMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'new_messages' => $newMessages]);
} catch (Exception $e) {
    error_log("Erreur lors de la vérification des nouveaux messages : " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la vérification des nouveaux messages.']);
}
?>
