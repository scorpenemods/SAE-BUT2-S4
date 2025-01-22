<?php
// manage notification as seen

// Démarre la session au début du script pour accéder aux variables de session
session_start();


// Inclut les fichiers nécessaires, notamment la classe Database pour la gestion de la base de données
require_once "../Model/Database.php";

// Définit le type de contenu de la réponse en JSON pour le retour d'API
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté en vérifiant la présence de la session utilisateur
if (isset($_SESSION['user'])) {
    // Récupère l'objet Person stocké en session
    $person = unserialize($_SESSION['user']);

    // Récupère l'ID de l'utilisateur depuis la session
    $userId = $_SESSION['user_id'];

    // Instancie l'objet Database (utilise le singleton pour éviter plusieurs connexions)
    $database = Database::getInstance();

    // Marque toutes les notifications comme vues pour l'utilisateur actuel
    $success = $database->markAllNotificationsAsSeen($userId);

    // Retourne le résultat de l'opération sous forme de JSON
    echo json_encode(['success' => $success]);
} else {
    // Si l'utilisateur n'est pas connecté, renvoie un message d'échec
    echo json_encode(['success' => false]);
}

