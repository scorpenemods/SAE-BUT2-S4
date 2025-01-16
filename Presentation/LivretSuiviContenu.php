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
if (empty($_SESSION['csrf_rapport'])) {
    $_SESSION['csrf_rapport'] = bin2hex(random_bytes(16));
}

$groupId = $database->getGroup($studentInfo['id'], $professorInfo['id'], $mentorInfo['id']);
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

?>
<!-- Changer le style pour que les formulaires s'affichent à côté des rencontres  -->
<aside class="livretbar">
    <h3 style="text-decoration: underline;"><?= $translations['rencontres']?> / <?= $translations['dépôts']?></h3><br>
    <span class="vignette" onclick="showContent(0)"><?= $translations['rencontre']?> 1</span><br>

    <span class="vignette" onclick="showContent(1)"><?= $translations['finalisation livret']?></span><br>

    <button onclick="addMeeting()" type="button">+ <?= $translations['ajouter rencontre']?></button>

    <button onclick="deleteMeeting()" type="button">- <?= $translations['supprimer rencontre']?></button>
</aside>

<!-- Affichage des informations des participants : -->

<div class="content-livret">

    <!-- Les différents formulaire pour chaque rencontre : -->

    <!-- Rencontre 1 -->
    <div class="content-section" id="0">
        <h3 style="padding: 10px"><?= $translations['formulaire']?></h3>
        <div class="livret-header">
            <h3><?= $translations['rencontre']?> 1</h3>
        </div>

        <!-- Formulaire -->
        <div class="participants">
            <form method="post" id="formContainer-0">
                <p>
                    <?= $translations['date'] . $translations['rencontre']?> : <label style="color: red">*</label> <br>

                    <input type="date" name="meeting"/>
                </p>

                <br><br>

                <p>
                    <?= $translations['lieu'] . $translations['rencontre']?> : <label style="color: red">*</label> <br>

                    <input type="radio" id="Entreprise" name="Lieu"><label> <?= $translations['en entreprise']?></label> <br>
                    <input type="radio" id="Tél" name="Lieu"><label> <?= $translations['par téléphone']?></label> <br>
                    <input type="radio" id="Visio" name="Lieu"><label> <?= $translations['en visio']?></label> <br>
                    <input type="radio" id="Autre" name="Lieu"><label> <?= $translations['autre']?></label> <input type="text" name="Lieu">
                </p>

                <br><br>

                <button onclick="addField('formContainer-0')" type="button">+ <?= $translations['ajouter champ']?></button>

            </form>
        </div>
        <div style="display: flex; ">
            <!-- Validation du formulaire -->
            <div class="validation">
                <h3 style="padding: 10px">Validation du formulaire</h3>

                <button>Valider modifications</button>
            </div>
        </div>
    </div>

    <!-- Bilan -->
    <div class="content-section" id="1">
        <h3 style="padding: 10px">Bilan/dépôt du rapport</h3>
        <div class="livret-header">
            <h3>Finalisation du livret</h3>
        </div>

        <div class="participants">
            <h2>Tableau des Compétences Acquises</h2>
            <form method="post" id="formContainer-1">
                <table class="tableau">
                    <thead>
                    <tr class="trEdit">
                        <th class="thEdit">Compétence</th>
                        <th class="thEdit">Niveau de Maîtrise</th>
                        <th class="thEdit">Commentaires</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="trEdit">
                        <td class="tdEdit">Adaptation à l'entreprise</td>
                        <td class="tdEdit">
                            <select class="selection" name="option1" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td class="tdEdit"><input class="tableInput" type="text" name="table1"></td>
                    </tr>
                    <tr class="trEdit">
                        <td class="tdEdit">Ponctualité</td>
                        <td class="tdEdit">
                            <select class="selection" name="option2" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td class="tdEdit"><input class="tableInput" type="text" name="table2"></td>
                    </tr>
                    <tr class="trEdit">
                        <td class="tdEdit">Motivation pour le travail</td>
                        <td class="tdEdit">
                            <select class="selection" name="option3" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td class="tdEdit"><input class="tableInput" type="text" name="table3"></td>
                    </tr>
                    <tr class="trEdit">
                        <td class="tdEdit">Initiatives personnelles</td>
                        <td class="tdEdit">
                            <select class="selection" name="option4" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td class="tdEdit"><input class="tableInput" type="text" name="table4"></td>
                    </tr>
                    <tr class="trEdit">
                        <td class="tdEdit">Qualité du travail</td>
                        <td class="tdEdit">
                            <select class="selection" name="option5" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td class="tdEdit"><input class="tableInput" type="text" name="table5"></td>
                    </tr>
                    <tr class="trEdit">
                        <td class="tdEdit">Intérêt pour la découverte de l'entreprise</td>
                        <td class="tdEdit">
                            <select class="selection" name="option6" onchange="removeDefaultOption(this)">
                                <option value="" selected>Aucun niveau sélectionné</option>
                                <option value="beginner">Débutant</option>
                                <option value="mid">Intermédiaire</option>
                                <option value="Advance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </td>
                        <td class="tdEdit"><input class="tableInput" type="text" name="table6"></td>
                    </tr>
                    </tbody>
                </table><br>

                <strong>Commentaires du professeur tuteur : </strong>
                <textarea name="remarque[]" class="textareaLivret"></textarea> <br><br>

                <strong>Commentaires du maitre de stage : </strong>
                <textarea name="remarque[]" class="textareaLivret"></textarea> <br><br>
            </form>

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
        <div style="display: flex; ">
            <!-- Validation du formulaire -->
            <div class="validation">
                <h3 style="padding: 10px">Validation</h3>

                <button>Finaliser le livret de suivi</button>
            </div>
        </div>
    </div>
</div>