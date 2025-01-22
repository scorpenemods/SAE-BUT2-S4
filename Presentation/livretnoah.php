<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../Model/Database.php';
require_once '../Model/Person.php';

$database = Database::getInstance();

if (!isset($_SESSION['user'])) {
    echo "Veuillez vous connecter avant d'accéder au livret de suivi.";
    exit;
}

$userId = $_SESSION['student_id'];
$role = $_SESSION['user_role']; // Ex: Prof (role=2)

$group = $database->getGroupByUserId($userId);
if (!$group) {
    echo "<p>Aucun groupe trouvé pour l'utilisateur #$userId.</p>";
    exit;
}

$conv_id = $group['conv_id'];
if (empty($conv_id)) {
    echo "<p>Le groupe existe, mais 'conv_id' est NULL. Impossible de créer/récupérer le FollowUpBook.</p>";
    exit;
}

$followUpId = $database->getOrCreateFollowUpBook($conv_id);

// -------------------------------------------------------------------
// Gestion des actions (POST)
// -------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            // -- Gestion des réunions
            case 'create_meeting':
                if ($role == 2) {
                    $name = trim($_POST['meeting_name']);
                    $startDate = $_POST['start_date'];
                    $endDate = $_POST['end_date'];
                    $database->insertMeetingBook($followUpId, $name, $startDate, $endDate, null, 0);
                }
                break;

            case 'delete_meeting':
                if ($role == 2) {
                    $meetingId = (int)$_POST['meeting_id'];
                    $database->deleteMeeting($meetingId);
                }
                break;

            // -- Gestion des compétences
            case 'add_competence':
                if ($role == 2) {
                    $competenceName = trim($_POST['competence_name']);
                    $database->insertCompetenceBilan($followUpId, $competenceName, 'Aucun niveau', '');
                }
                break;

            case 'valider_bilan':
                // On met à jour chaque compétence
                $competences = $database->getCompetencesByFollowUpId($followUpId);
                foreach ($competences as $c) {
                    $niveau = $_POST['option' . $c['id']] ?? 'Aucun niveau';
                    $commentaire = $_POST['commentaire' . $c['id']] ?? '';
                    $database->updateCompetenceBilan($c['id'], $niveau, $commentaire);
                }
                break;

            // -- Gestion des textes
            case 'add_text':
                $meetingId = (int)$_POST['meeting_id'];
                $title = trim($_POST['title']);
                $response = trim($_POST['response']);
                if ($title !== '') {
                    $database->insertMeetingText($meetingId, $title, $response);
                }
                break;

            case 'update_text':
                $textId = (int)$_POST['text_id'];
                $newResponse = trim($_POST['response']);
                $database->updateMeetingText($textId, $newResponse);
                break;

            case 'delete_text':
                $textId = (int)$_POST['text_id'];
                $database->deleteMeetingText($textId);
                break;

            // -- Gestion des QCM
            case 'add_qcm':
                $meetingId = (int)$_POST['meeting_id'];
                $qcmTitle = trim($_POST['qcm_title']);
                $otherChoice = trim($_POST['other_choice']);
                if ($qcmTitle !== '') {
                    $database->insertMeetingQCM($meetingId, $qcmTitle, '', $otherChoice);
                }
                break;

            case 'update_qcm':
                $qcmId = (int)$_POST['qcm_id'];
                $newChoice = trim($_POST['other_choice']);
                $database->updateMeetingQCM($qcmId, $newChoice);
                break;

            case 'delete_qcm':
                $qcmId = (int)$_POST['qcm_id'];
                $database->deleteMeetingQCM($qcmId);
                break;
        }
        // Après traitement, on recharge la page pour éviter le renvoi du formulaire
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// -------------------------------------------------------------------
// Récupérer les rencontres et compétences existantes
// -------------------------------------------------------------------
$meetings = $database->getMeetingsByFollowUp($followUpId);
$competenceBilan = $database->getCompetencesByFollowUpId($followUpId);

?>
