<?php
// Démarre la session pour accéder aux variables de session utilisateur
session_start();

// Inclut le fichier Database.php pour gérer les interactions avec la base de données
require_once "../Model/Database.php";

// Définit le type de réponse comme JSON pour indiquer qu'une réponse JSON sera renvoyée
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté en regardant si la variable de session 'user' est définie
if (isset($_SESSION['user'])) {
    // Récupère l'objet utilisateur stocké en session
    $person = unserialize($_SESSION['user']);

    // Récupère l'ID de l'utilisateur depuis la session
    $userId = $_SESSION['user_id'];

    // Instancie la base de données en utilisant le modèle singleton
    $database = Database::getInstance();

    // Obtient le nombre de notifications non lues pour cet utilisateur
    $unreadCount = $database->getUnreadNotificationCount($userId);

    // Vérifie s'il y a de nouvelles notifications depuis la dernière vérification
    $lastCheck = $_SESSION['last_notification_check'] ?? null; // Récupère la dernière vérification en session, ou null si non définie
    $newNotifications = false; // Initialise la variable indiquant s'il y a de nouvelles notifications

    if ($lastCheck) {
        // Vérifie s'il y a des notifications ajoutées après le dernier contrôle
        $newNotifications = $database->hasNewNotifications($userId, $lastCheck);
    } else if ($unreadCount > 0) {
        // Si c'est la première vérification et qu'il y a des notifications non lues, considère qu'il y a de nouvelles notifications
        $newNotifications = true;
    }

    // Met à jour la dernière heure de vérification des notifications dans la session
    $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');

    // Renvoie la réponse JSON avec le nombre de notifications non lues et s'il y a de nouvelles notifications
    echo json_encode([
        'unreadCount' => $unreadCount,
        'newNotifications' => $newNotifications
    ]);
} else {
    // Si l'utilisateur n'est pas connecté, renvoie zéro notification et indique qu'il n'y a pas de nouvelles notifications
    echo json_encode(['unreadCount' => 0, 'newNotifications' => false]);
}
?>
