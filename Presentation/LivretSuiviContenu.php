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
<aside class="livretbar">
    <h3 style="text-decoration: underline;"><?= $translations['rencontres']?> / <?= $translations['dépôts']?></h3>
    <br>
    <button onclick="addMeeting()" type="button" style="margin-bottom:10px;">
        + <?= $translations['ajouter rencontre']?>
    </button>
    <button onclick="deleteMeeting()" type="button" style="margin-bottom:10px;">
        - <?= $translations['supprimer rencontre']?>
    </button>
</aside>

<!-- Contenu dynamique généré par JS (Rencontres) + Bilan -->
<div class="content-livret" style="flex:1; padding:10px;">
    <!-- Изначально пусто, всё генерирует LivretSuivi.js (функция loadExistingMeetings()).
         Плюс блок "Bilan" (id=BilanSection) для finalisation. -->

    <!-- BILAN / Finalisation -->
    <div class="content-section" id="BilanSection">
        <h3 style="padding: 10px">Bilan / Dépôt du rapport</h3>
        <div class="participants">
            <h2>Tableau des Compétences Acquises</h2>
            <form method="post" id="formContainer-bilan">
                <input type="hidden" name="followup_id" value="<?php echo (int)$followUpId; ?>">
                <table class="tableau">
                    <thead>
                    <tr>
                        <th>Compétence</th>
                        <th>Niveau de Maîtrise</th>
                        <th>Commentaires</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Adaptation à l'entreprise</td>
                        <td>
                            <select name="option1" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td><input type="text" name="table1"></td>
                    </tr>
                    <tr>
                        <td>Ponctualité</td>
                        <td>
                            <select name="option2" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td><input type="text" name="table2"></td>
                    </tr>
                    <tr>
                        <td>Motivation pour le travail</td>
                        <td>
                            <select name="option3" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td><input type="text" name="table3"></td>
                    </tr>
                    <tr>
                        <td>Initiatives personnelles</td>
                        <td>
                            <select name="option4" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td><input type="text" name="table4"></td>
                    </tr>
                    <tr>
                        <td>Qualité du travail</td>
                        <td>
                            <select name="option5" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td><input type="text" name="table5"></td>
                    </tr>
                    <tr>
                        <td>Intérêt pour la découverte de l'entreprise</td>
                        <td>
                            <select name="option6" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td><input type="text" name="table6"></td>
                    </tr>
                    </tbody>
                </table>
                <br>
                <strong>Commentaires du professeur tuteur :</strong><br>
                <textarea name="remarque[]" class="textareaLivret"></textarea> <br><br>
                <strong>Commentaires du maitre de stage :</strong><br>
                <textarea name="remarque[]" class="textareaLivret"></textarea> <br><br>

                <button type="button" class="validate-bilan-btn" style="background: #38761d; color:white; padding:6px 12px;">
                    Valider le Bilan
                </button>
            </form>

            <?php if ($userRole == 1): // Étudiant ?>
                <h3 style="margin-top: 30px;">Veuillez déposer votre rapport de stage ci-dessous :</h3>
                <form class="box" method="post" enctype="multipart/form-data">
                    <p>Seuls les formats .pdf sont autorisés (max 50 Mo).</p>
                    <div class="box__input">
                        <input type="file" name="file" id="fileUpload">
                        <button class="box__button" type="submit">Uploader</button>
                    </div>
                </form>
            <?php endif; ?>

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
            $db = Database::getInstance();
            $rapportfiles = $db->getLivretFile($groupId);
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