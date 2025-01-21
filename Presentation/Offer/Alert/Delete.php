<?php
// File: Delete.php
// Delete an Alert
session_start();

require_once dirname(__FILE__) . "/../../../Model/Database.php";
$db = Database::getInstance();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Utilisateur non connecté."]);
    exit;
}

$userId = $_SESSION['user_id'];

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

