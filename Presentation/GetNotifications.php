<?php
// Démarre la session pour accéder aux informations de l'utilisateur
session_start();

// Inclut le fichier Database.php pour les interactions avec la base de données
require_once "../Model/Database.php";

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté en vérifiant la variable de session 'user'
if (isset($_SESSION['user'])) {
    // Récupère l'objet utilisateur depuis la session
    $person = unserialize($_SESSION['user']);

    // Récupère l'ID de l'utilisateur depuis la session
    $userId = $_SESSION['user_id'];

    // Instancie l'objet Database en utilisant le modèle singleton
    $database = Database::getInstance();

    // Récupère les notifications de l'utilisateur depuis la base de données
    $notifications = $database->getNotifications($userId);

    // Formate les dates des notifications pour un affichage en jour/mois/année heure:minute
    foreach ($notifications as &$notification) {
        $notification['created_at'] = date('d/m/Y H:i', strtotime($notification['created_at']));
    }

    // Encode les notifications en JSON pour la réponse
    echo json_encode(['notifications' => $notifications]);
} else {
    // Si l'utilisateur n'est pas connecté, retourne une réponse JSON vide pour les notifications
    echo json_encode(['notifications' => []]);
}
?>
