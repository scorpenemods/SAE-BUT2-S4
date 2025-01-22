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
$role = 2;

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

// Gestion des actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
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

            case 'add_competence':
                if ($role == 2) {
                    $competenceName = trim($_POST['competence_name']);
                    $db->insertCompetenceBilan($followUpId, $competenceName, 'Aucun niveau', '');
                }
                break;

            case 'valider_bilan':
                $competences = $db->getCompetencesByFollowUpId($followUpId);
                foreach ($competences as $c) {
                    $niveau = $_POST['option' . $c['id']] ?? 'Aucun niveau';
                    $commentaire = $_POST['commentaire' . $c['id']] ?? '';
                    $db->updateCompetenceBilan($c['id'], $niveau, $commentaire);
                }
                break;
        }
        header("Location: livretnoah.php");
        exit;
    }
}

// Récupérer les rencontres et compétences existantes
$meetings = $db->getMeetingsByFollowUp($followUpId);
$competenceBilan = $db->getCompetencesByFollowUpId($followUpId);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livret de Stage - Suivi</title>
    <link rel="stylesheet" href="../View/Principal/Principal.css">
</head>
<body>

<div class="content-livret">
    <h2>Gestion du Livret de Suivi</h2>

    <div class="actions">
        <?php if ($role == 2): ?>
            <button onclick="document.getElementById('addMeetingForm').style.display='block'">+ Ajouter une Rencontre</button>
            <button onclick="document.getElementById('addCompetenceForm').style.display='block'">+ Ajouter Compétence</button>
        <?php endif; ?>
    </div>

    <!-- Formulaire pour ajouter une rencontre -->
    <div id="addMeetingForm" style="display:none;">
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

    <!-- Formulaire pour ajouter des compétences -->
    <div id="addCompetenceForm" style="display:none;">
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
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete_meeting">
                            <input type="hidden" name="meeting_id" value="<?= $m['id'] ?>">
                            <button type="submit" onclick="return confirm('Supprimer cette rencontre ?')">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Gestion des compétences -->
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
                        <td><input type="text" name="commentaire<?= $c['id'] ?>" value="<?= htmlspecialchars($c['commentaire']) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="validate-bilan-btn">Valider le Bilan</button>
        </form>
    </div>

</div>

</body>
</html>
