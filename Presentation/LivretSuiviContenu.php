<?php
require_once '../Model/Database.php';
require_once '../Model/Person.php';

$database = Database::getInstance();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$person = unserialize($_SESSION['user']);
$userRole = $person->getRole();

// Générer un jeton CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// We get from $GLOBALS
$studentInfo   = $GLOBALS['studentInfo'] ?? null;
$professorInfo = $GLOBALS['professorInfo'] ?? null;
$mentorInfo    = $GLOBALS['mentorInfo'] ?? null;
$followUpId    = $GLOBALS['followUpId'] ?? 0;
$existingMeetingsCount = $GLOBALS['meetingsCount'] ?? 0;

// compute groupId:
$groupId = 0;
if (!empty($studentInfo) && !empty($professorInfo) && !empty($mentorInfo)) {
    $groupId = $database->getGroup(
        $studentInfo['id'],
        $professorInfo['id'],
        $mentorInfo['id']
    );
}
$_SESSION['student_id']= $studentInfo['id'];
$_SESSION['group_id'] = $groupId;

//TRADUCTION

// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = "../locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../locales/fr.php";
}

// Charger les traductions
$translations = include $langFile;
$file = $database->getLivretFile($groupId);

?>

<!-- Contenu dynamique généré par JS (Rencontres) + Bilan -->
<div class="content-livret" style="flex:1; padding:10px;">
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

    <!-- BILAN / Finalisation -->
    <div class="content-section" id="BilanSection">
        <h3 style="padding: 10px">Bilan / Dépôt du rapport</h3>
        <div class="participants">

            <?php
            require_once "livretnoah.php";
            ?>




            <style>
                .meeting-item { margin-bottom:10px; }
                .texte-item, .qcm-item { margin: 5px 0; }
                .hidden { display: none; }
                .content-section { border: 1px solid #ccc; padding:10px; margin:10px 0; }
            </style>

            <div class="content-livret">
                <h2>Gestion du Livret de Suivi</h2>

                <div class="actions">
                    <?php if ($role == 2): ?>
                        <!-- Professeur : peut ajouter une rencontre et ajouter une compétence -->
                        <button onclick="document.getElementById('addRencontre').style.display='block'">+ Ajouter une Rencontre</button>
                        <button onclick="document.getElementById('addCompetenceForm').style.display='block'">+ Ajouter Compétence</button>
                    <?php endif; ?>
                </div>

                <!-- Formulaire : Ajouter une rencontre -->
                <div id="addRencontre" class="hidden">
                    <h3>Ajouter une nouvelle rencontre</h3>
                    <form method="post" action="livretnoah.php">
                        <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="action" value="create_meeting">
                        <label>Nom de la rencontre :</label>
                        <input type="text" name="meeting_name" required>

                        <label>Date de début :</label>
                        <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required>

                        <label>Date de fin :</label>
                        <input type="date" name="end_date" value="<?= date('Y-m-d', strtotime('+1 month')) ?>" required>

                        <button type="submit">Ajouter</button>
                        <button type="button" onclick="document.getElementById('addRencontre').style.display='none'">Annuler</button>
                    </form>
                </div>

                <!-- Formulaire : Ajouter une compétence -->
                <div id="addCompetenceForm" class="hidden">
                    <h3>Ajouter une Compétence</h3>
                    <form method="post" action="livretnoah.php">
                        <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
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
                                    <form method="post" style="display:inline;" action="livretnoah.php">
                                        <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
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
                                $texts = $database->getTextsByMeeting($m['id']);
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
                                                <form method="post" style="display:inline;" action="livretnoah.php">
                                                    <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                                                    <input type="hidden" name="action" value="update_text">
                                                    <input type="hidden" name="text_id" value="<?= $text['id'] ?>">
                                                    <input type="text" name="response" placeholder="Modifier la réponse">
                                                    <button type="submit">Mettre à jour</button>
                                                </form>

                                                <!-- Supprimer le texte -->
                                                <form method="post" style="display:inline;" action="livretnoah.php">
                                                    <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                                                    <input type="hidden" name="action" value="delete_text">
                                                    <input type="hidden" name="text_id" value="<?= $text['id'] ?>">
                                                    <button type="submit" onclick="return confirm('Supprimer ce texte ?')">Supprimer</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <!-- Formulaire d'ajout d'un nouveau texte -->
                                <form method="post" action="livretnoah.php">
                                    <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
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
                                $qcms = $database->getQCMByMeeting($m['id']);
                                if (!$qcms): ?>
                                    <p>Aucun QCM enregistré.</p>
                                <?php else: ?>
                                    <?php foreach ($qcms as $qcm): ?>
                                        <div class="qcm-item">
                                            <strong><?= htmlspecialchars($qcm['title']) ?></strong><br>
                                            Réponse libre : <?= nl2br(htmlspecialchars($qcm['other_choice'])) ?>

                                            <?php if ($role == 2): ?>
                                                <!-- Update QCM -->
                                                <form method="post" style="display:inline;" action="livretnoah.php">
                                                    <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                                                    <input type="hidden" name="action" value="update_qcm">
                                                    <input type="hidden" name="qcm_id" value="<?= $qcm['id'] ?>">
                                                    <input type="text" name="other_choice" placeholder="Modifier la réponse libre">
                                                    <button type="submit">Mettre à jour</button>
                                                </form>

                                                <!-- Delete QCM -->
                                                <form method="post" style="display:inline;" action="livretnoah.php">
                                                    <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                                                    <input type="hidden" name="action" value="delete_qcm">
                                                    <input type="hidden" name="qcm_id" value="<?= $qcm['id'] ?>">
                                                    <button type="submit" onclick="return confirm('Supprimer ce QCM ?')">Supprimer</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <!-- Formulaire d'ajout d'un nouveau QCM -->
                                <form method="post" action="livretnoah.php">
                                    <input type="hidden" name="redirect_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
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
                    <form method="post" action="livretnoah.php">
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

            <?php include_once("Documents/Documents.php");?>

            <h2>Gestion des Rapports</h2>
            <form class="box" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="form_id" value="uploader_rapport">
                <input type="hidden" name="groupId" value="<?=$groupId?>">
                <div class="box__input">
                    <input type="file" name="files[]" id="file-rapport" multiple>
                    <button class="box__button" type="submit">Uploader Rapport</button>
                </div>
            </form>

            <?php
            $rapportfiles = $database->getLivretFile($groupId);
            ?>
            <div class="file-list">
                <h2>Fichiers Uploadés</h2>
                <div class="file-grid">
                    <?php foreach ($rapportfiles as $rapportfiles): ?>
                        <div class="file-card">
                            <div class="file-info">
                                <strong><?= htmlspecialchars($rapportfiles['name']) ?></strong>
                                <p><?= round($rapportfiles['size'] / 1024, 2) ?> KB</p>
                            </div>
                            <form method="get" action="Documents/Download.php">
                                <input type="hidden" name="file" value="<?= htmlspecialchars($rapportfiles['path']) ?>">
                                <button type="submit" class="download-button">Télécharger</button>
                            </form>
                            <form method="post" action="" class="delete-form">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="form_id" value="delete_rapport">
                                <input type="hidden" name="fileId" value="<?= $rapportfiles['id'] ?>">
                                <button type="submit" class="delete-button">Supprimer</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div> <!-- fin #BilanSection -->
</div>