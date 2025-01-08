<?php
// File: Create.php
// Create an Alert

session_start();

require_once dirname(__FILE__) . '/../../../Model/Offer.php';
$db = Database::getInstance()->getConnection();

if (!isset($_SESSION['user'])) {
    echo json_encode(["status" => "error", "message" => "Utilisateur non connecté."]);
    exit;
}

$userId = $_SESSION['user'];

$duration = filter_input(INPUT_POST, 'duration', FILTER_VALIDATE_INT);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
$studyLevel = filter_input(INPUT_POST, 'study_level', FILTER_SANITIZE_SPECIAL_CHARS);
$salary = filter_input(INPUT_POST, 'salary', FILTER_VALIDATE_INT);
$beginDate = filter_input(INPUT_POST, 'begin_date', FILTER_SANITIZE_SPECIAL_CHARS);

if ($duration == null && $address == null && $studyLevel == null && $salary == null && $beginDate == null) {
    echo json_encode(["status" => "error", "message" => "Aucun filtre valide fourni."]);
    exit;
}

try {
    $database = (Database::getInstance());
    $database->addAlert($userId, $duration, $address, $studyLevel, $salary, $beginDate);

    echo json_encode(["status" => "success", "message" => "Notification créée avec succès."]);
}
catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur serveur : " . $e->getMessage()]);
}

