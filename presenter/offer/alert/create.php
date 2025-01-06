<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/models/Database.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Utilisateur non connecté."]);
    exit;
}

$userId = $_SESSION['user'];

$duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
$study_level = filter_input(INPUT_POST, 'study_level', FILTER_SANITIZE_SPECIAL_CHARS);
$salary = filter_input(INPUT_POST, 'salary', FILTER_VALIDATE_INT);
$begin_date = filter_input(INPUT_POST, 'begin_date', FILTER_SANITIZE_SPECIAL_CHARS);

if ($duration == null && $address == null && $study_level == null && $salary == null && $begin_date == null) {
    echo json_encode(["status" => "error", "message" => "Aucun filtre valide fourni."]);
    exit;
}

try {
    $database = (Database::getInstance());
    $database->addAlert($userId, $duration, $address, $study_level, $salary, $begin_date);

    echo json_encode(["status" => "success", "message" => "Notification créée avec succès."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur serveur : " . $e->getMessage()]);
}