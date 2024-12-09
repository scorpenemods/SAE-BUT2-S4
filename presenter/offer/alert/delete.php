<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Utilisateur non connecté."]);
    exit;
}

$userId = $_SESSION['user'];

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id == null || $id == 0) {
    echo json_encode(["status" => "error", "message" => "Aucun filtre valide fourni."]);
    exit;
}

try {
    $database = (Database::getInstance());
    $database->deleteAlert($id);

    echo json_encode(["status" => "success", "message" => "Notification supprimée avec succès."]);
}
catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur serveur : " . $e->getMessage()]);
}

