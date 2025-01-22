<?php
session_start();
require_once '../Model/Database.php';
require_once '../Model/Person.php';

$db = Database::getInstance();

if (!isset($_SESSION['user'])) {
    echo "Veuillez vous connecter avant d'accéder au livret de suivi.";
    exit;
}

$person = unserialize($_SESSION['user']);
$userId = 7;
$role = 2; // Ex: Prof (role=2)

$group = $db->getGroupByUserId($userId);
if (!$group) {
    echo "<p>Aucun groupe trouvé pour l'utilisateur #$userId.</p>";
    exit;
}

$conv_id = $group['conv_id'];
if (empty($conv_id)) {
    echo "<p>Le groupe existe, mais 'conv_id' est NULL. Impossible de créer/récupérer le FollowUpBook.</p>";
    exit;
}

$followUpId = $db->getOrCreateFollowUpBook($conv_id);

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
                    $db->insertMeetingBook($followUpId, $name, $startDate, $endDate, null, 0);
                }
                break;

            case 'delete_meeting':
                if ($role == 2) {
                    $meetingId = (int)$_POST['meeting_id'];
                    $db->deleteMeeting($meetingId);
                }
                break;

            // -- Gestion des compétences
            case 'add_competence':
                if ($role == 2) {
                    $competenceName = trim($_POST['competence_name']);
                    $db->insertCompetenceBilan($followUpId, $competenceName, 'Aucun niveau', '');
                }
                break;

            case 'valider_bilan':
                // On met à jour chaque compétence
                $competences = $db->getCompetencesByFollowUpId($followUpId);
                foreach ($competences as $c) {
                    $niveau = $_POST['option' . $c['id']] ?? 'Aucun niveau';
                    $commentaire = $_POST['commentaire' . $c['id']] ?? '';
                    $db->updateCompetenceBilan($c['id'], $niveau, $commentaire);
                }
                break;

            // -- Gestion des textes
            case 'add_text':
                $meetingId = (int)$_POST['meeting_id'];
                $title = trim($_POST['title']);
                $response = trim($_POST['response']);
                if ($title !== '') {
                    $db->insertMeetingText($meetingId, $title, $response);
                }
                break;

            case 'update_text':
                $textId = (int)$_POST['text_id'];
                $newResponse = trim($_POST['response']);
                $db->updateMeetingText($textId, $newResponse);
                break;

            case 'delete_text':
                $textId = (int)$_POST['text_id'];
                $db->deleteMeetingText($textId);
                break;

            // -- Gestion des QCM
            case 'add_qcm':
                $meetingId = (int)$_POST['meeting_id'];
                $qcmTitle = trim($_POST['qcm_title']);
                $otherChoice = trim($_POST['other_choice']);
                if ($qcmTitle !== '') {
                    $db->insertMeetingQCM($meetingId, $qcmTitle, '', $otherChoice);
                }
                break;

            case 'update_qcm':
                $qcmId = (int)$_POST['qcm_id'];
                $newChoice = trim($_POST['other_choice']);
                $db->updateMeetingQCM($qcmId, $newChoice);
                break;

            case 'delete_qcm':
                $qcmId = (int)$_POST['qcm_id'];
                $db->deleteMeetingQCM($qcmId);
                break;
        }
        // Après traitement, on recharge la page pour éviter le renvoi du formulaire
        header("Location: livretnoah.php");
        exit;
    }
}

// -------------------------------------------------------------------
// Récupérer les rencontres et compétences existantes
// -------------------------------------------------------------------
$meetings = $db->getMeetingsByFollowUp($followUpId);
$competenceBilan = $db->getCompetencesByFollowUpId($followUpId);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livret de Stage - Suivi</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
    <style>
        .meeting-item { margin-bottom:10px; }
        .texte-item, .qcm-item { margin: 5px 0; }
        .hidden { display: none; }
        .content-section { border: 1px solid #ccc; padding:10px; margin:10px 0; }
    </style>
</head>
<body>

<div class="content-livret">
    <h2>Gestion du Livret de Suivi</h2>

    <div class="actions">
        <?php if ($role == 2): ?>
            <!-- Professeur : peut ajouter une rencontre et ajouter une compétence -->
            <button onclick="document.getElementById('addMeetingForm').style.display='block'">+ Ajouter une Rencontre</button>
            <button onclick="document.getElementById('addCompetenceForm').style.display='block'">+ Ajouter Compétence</button>
        <?php endif; ?>
    </div>

    <!-- Formulaire : Ajouter une rencontre -->
    <div id="addMeetingForm" class="hidden">
        <h3>Ajouter une nouvelle rencontre</h3>
        <form method="post">
            <input type="hidden" name="action" value="create_meeting">
            <label>Nom de la rencontre :</label>
            <input type="text" name="meeting_name" required>

            <label>Date de début :</label>
            <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required>

            <label>Date de fin :</label>
            <input type="date" name="end_date" value="<?= date('Y-m-d', strtotime('+1 month')) ?>" required>

            <button type="submit">Ajouter</button>
            <button type="button" onclick="document.getElementById('addMeetingForm').style.display='none'">Annuler</button>
        </form>
    </div>

    <!-- Formulaire : Ajouter une compétence -->
    <div id="addCompetenceForm" class="hidden">
        <h3>Ajouter une Compétence</h3>
        <form method="post">
            <input type="hidden" name="action" value="add_competence">
            <label>Nom de la compétence :</label>
            <input type="text" name="competence_name" required>

            <button type="submit">Ajouter</button>
            <button type="button" onclick="document.getElementById('addCompetenceForm').style.display='none'">Annuler</button>
        </form>
    </div>

    <!-- Liste des rencontres -->
    <div class="content-section">
        <h3>Rencontres Planifiées</h3>
        <?php if (!$meetings): ?>
            <p>Aucune rencontre planifiée.</p>
        <?php else: ?>
            <?php foreach ($meetings as $m): ?>
                <div class="meeting-item">
                    <strong><?= htmlspecialchars($m['name']) ?></strong>
                    (du <?= $m['start_date'] ?> au <?= $m['end_date'] ?>)

                    <?php if ($role == 2): ?>
                        <!-- Supprimer la rencontre (professeur) -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete_meeting">
                            <input type="hidden" name="meeting_id" value="<?= $m['id'] ?>">
                            <button type="submit" onclick="return confirm('Supprimer cette rencontre ?')">Supprimer</button>
                        </form>
                    <?php endif; ?>

                    <!-- Bouton Détails -->
                    <button onclick="toggleDetails(<?= $m['id'] ?>)">Détails</button>
                </div>

                <!-- Zone Détails de la rencontre -->
                <div id="meeting-details-<?= $m['id'] ?>" class="hidden" style="margin-left:30px;">
                    <h4>Détails de la rencontre</h4>

                    <!-- Liste des textes existants -->
                    <h5>Discussions/Textes</h5>
                    <?php
                    $texts = $db->getTextsByMeeting($m['id']);
                    if (!$texts): ?>
                        <p>Aucun texte enregistré.</p>
                    <?php else: ?>
                        <?php foreach ($texts as $text): ?>
                            <div class="texte-item">
                                <strong><?= htmlspecialchars($text['title']) ?></strong> :
                                <?= nl2br(htmlspecialchars($text['response'])) ?>

                                <!-- Boutons éditer / supprimer (optionnel, selon role) -->
                                <?php if ($role == 2): ?>
                                    <!-- Mettre à jour le texte -->
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="update_text">
                                        <input type="hidden" name="text_id" value="<?= $text['id'] ?>">
                                        <input type="text" name="response" placeholder="Modifier la réponse">
                                        <button type="submit">Mettre à jour</button>
                                    </form>

                                    <!-- Supprimer le texte -->
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_text">
                                        <input type="hidden" name="text_id" value="<?= $text['id'] ?>">
                                        <button type="submit" onclick="return confirm('Supprimer ce texte ?')">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Formulaire d'ajout d'un nouveau texte -->
                    <form method="post">
                        <input type="hidden" name="action" value="add_text">
                        <input type="hidden" name="meeting_id" value="<?= $m['id'] ?>">
                        <label>Titre :</label>
                        <input type="text" name="title" required>
                        <label>Réponse :</label>
                        <textarea name="response" required></textarea>
                        <button type="submit">Ajouter</button>
                    </form>

                    <hr>

                    <!-- QCM existants -->
                    <h5>QCM</h5>
                    <?php
                    $qcms = $db->getQCMByMeeting($m['id']);
                    if (!$qcms): ?>
                        <p>Aucun QCM enregistré.</p>
                    <?php else: ?>
                        <?php foreach ($qcms as $qcm): ?>
                            <div class="qcm-item">
                                <strong><?= htmlspecialchars($qcm['title']) ?></strong><br>
                                Réponse libre : <?= nl2br(htmlspecialchars($qcm['other_choice'])) ?>

                                <?php if ($role == 2): ?>
                                    <!-- Update QCM -->
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="update_qcm">
                                        <input type="hidden" name="qcm_id" value="<?= $qcm['id'] ?>">
                                        <input type="text" name="other_choice" placeholder="Modifier la réponse libre">
                                        <button type="submit">Mettre à jour</button>
                                    </form>

                                    <!-- Delete QCM -->
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_qcm">
                                        <input type="hidden" name="qcm_id" value="<?= $qcm['id'] ?>">
                                        <button type="submit" onclick="return confirm('Supprimer ce QCM ?')">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Formulaire d'ajout d'un nouveau QCM -->
                    <form method="post">
                        <input type="hidden" name="action" value="add_qcm">
                        <input type="hidden" name="meeting_id" value="<?= $m['id'] ?>">
                        <label>Question :</label>
                        <input type="text" name="qcm_title" required>
                        <label>Réponse libre :</label>
                        <input type="text" name="other_choice" required>
                        <button type="submit">Ajouter</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Gestion des compétences (Bilan) -->
    <div class="content-section">
        <h3>Bilan des Compétences</h3>
        <form method="post">
            <input type="hidden" name="action" value="valider_bilan">
            <table class="tableau">
                <thead>
                <tr>
                    <th>Compétence</th>
                    <th>Niveau de Maîtrise</th>
                    <th>Commentaires</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($competenceBilan as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['competence']) ?></td>
                        <td>
                            <select name="option<?= $c['id'] ?>">
                                <option value="Aucun niveau" <?= $c['niveau'] == 'Aucun niveau' ? 'selected' : '' ?>>Aucun niveau</option>
                                <option value="Débutant" <?= $c['niveau'] == 'Débutant' ? 'selected' : '' ?>>Débutant</option>
                                <option value="Intermédiaire" <?= $c['niveau'] == 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                                <option value="Avancé" <?= $c['niveau'] == 'Avancé' ? 'selected' : '' ?>>Avancé</option>
                                <option value="Expert" <?= $c['niveau'] == 'Expert' ? 'selected' : '' ?>>Expert</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="commentaire<?= $c['id'] ?>" value="<?= htmlspecialchars($c['commentaire']) ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="validate-bilan-btn">Valider le Bilan</button>
        </form>
    </div>

</div>

<script>
    function toggleDetails(meetingId) {
        let detailsDiv = document.getElementById('meeting-details-' + meetingId);
        if (detailsDiv.classList.contains('hidden')) {
            detailsDiv.classList.remove('hidden');
        } else {
            detailsDiv.classList.add('hidden');
        }
    }
</script>

</body>
</html>
