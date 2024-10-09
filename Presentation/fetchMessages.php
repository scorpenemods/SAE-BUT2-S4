<?php
// Démarre la session pour accéder aux données utilisateur
session_start();

// Inclusion du fichier Database pour interagir avec la base de données
require "../Model/Database.php";

// Récupère l'ID de l'utilisateur actuellement connecté à partir de la session
$senderId = $_SESSION['user_id'] ?? null;

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 4) {
    // Si l'utilisateur n'a pas le rôle requis (ici 4), on bloque l'accès
    header('location: AccessDenied.php');
    exit();
}

// Récupère l'ID du destinataire à partir de la requête POST (envoyé par un formulaire ou une requête AJAX)
$receiverId = $_POST['receiver_id'] ?? null;

// Vérifie si l'ID de l'expéditeur et celui du destinataire sont présents
if ($senderId && $receiverId) {
    // Crée une instance de la base de données
    $database = new Database();

    // Récupère les messages échangés entre l'expéditeur et le destinataire
    $messages = $database->getMessages($senderId, $receiverId);

    // Retourne une réponse JSON avec les messages et un statut de succès
    echo json_encode([
        'status' => 'success',
        'messages' => $messages
    ]);
} else {
    // Si les ID ne sont pas définis, retourne une erreur en JSON
    echo json_encode(['status' => 'error', 'message' => 'Utilisateur ou destinataire non défini.']);
}
?>
