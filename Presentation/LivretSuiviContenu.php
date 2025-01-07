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

$groupId = $database->getGroup($studentInfo['id'], $professorInfo['id'], $mentorInfo['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Configuration des paramètres d'upload
    $allowedExtensions = ['pdf'];
    $maxFileSize = 50 * 1024 * 1024; // 50 Mo en octets

    // Vérification que le fichier a été téléchargé
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];

        // Extraction des informations du fichier
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmpPath = $file['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Vérification de l'extension et de la taille
        if (!in_array($fileExtension, $allowedExtensions)) {
            die("Erreur : Seuls les fichiers .pdf sont autorisés.");
        }

        if ($fileSize > $maxFileSize) {
            die("Erreur : La taille du fichier dépasse la limite de 50 Mo.");
        }

        // Déplacement du fichier vers un répertoire permanent
        $uploadDir = 'uploads/'; // Répertoire où stocker les fichiers (créez-le si nécessaire)
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uniqueFileName = uniqid('file_', true) . '.' . $fileExtension;
        $destinationPath = $uploadDir . $uniqueFileName;

        if (move_uploaded_file($fileTmpPath, $destinationPath)) {
            // Appel de la méthode addLivretFile
            $result = $database->addLivretFile($fileName, $destinationPath, $studentInfo['id'], $fileSize, $groupId);

            if ($result) {
                echo "Fichier téléchargé et enregistré avec succès.";
            } else {
                echo "Erreur lors de l'enregistrement dans la base de données.";
            }
        } else {
            echo "Erreur : Échec du déplacement du fichier.";
        }
    } else {
        echo "Erreur : Aucun fichier téléchargé ou erreur lors de l'upload.";
    }
}

$file = $database->getLivretFile($groupId);


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
    <span class="vignette" onclick="showContent(0)">1ère rencontre</span><br>

    <span class="vignette" onclick="showContent(1)">Finalisation du livret</span><br>

    <button onclick="addMeeting()" type="button">+ Ajouter une rencontre</button>

    <button onclick="deleteMeeting()" type="button">- Supprimer la dernière rencontre</button>
</aside>

<!-- Affichage des informations des participants : -->

<div class="content-livret">

    <!-- Les différents formulaire pour chaque rencontre : -->

    <!-- Rencontre 1 -->
    <div class="content-section" id="0">
        <h3 style="padding: 10px">Formulaire</h3>
        <div class="livret-header">
            <h3>1ère rencontre</h3>
        </div>

        <!-- Formulaire -->
        <div class="participants">
            <form method="post" id="formContainer-0">
                <p>
                    Date de rencontre : <label style="color: red">*</label> <br>

                    <input type="date" name="meeting"/>
                </p>

                <br><br>

                <p>
                    Lieu de la rencontre : <label style="color: red">*</label> <br>

                    <input type="radio" id="Entreprise" name="Lieu"><label> En entreprise</label> <br>
                    <input type="radio" id="Tél" name="Lieu"><label> Par téléphone</label> <br>
                    <input type="radio" id="Visio" name="Lieu"><label> En visio</label> <br>
                    <input type="radio" id="Autre" name="Lieu"><label> Autre</label> <input type="text" name="Lieu">
                </p>

                <br><br>

                <button onclick="addField('formContainer-0')" type="button">+ Ajouter un champ</button>

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



            <?php
            //Vérifie que seulement l'étudiant peut voir le dépôt de fichiers
            if ($userRole == $userRole){
                ?>

                <h3 style="margin-bottom: 10px">Veuillez déposer votre rapport de stage ci-dessous :</h3>

                <form class="box" method="post" enctype="multipart/form-data">
                    <p>Seuls les formats <strong>.pdf</strong> sont autorisés jusqu'à une taille maximale de <strong>50 Mo</strong>. </p><br>
                    <div class="box__input">
                        <input type="file" name="file" id="fileUpload">
                        <button class="box__button" type="submit">Uploader</button>
                    </div>
                </form>
                <?php
            }   ?>

            <div class="file-list">
                <h2>Fichier(s) déposé(s) :</h2>
                <div class="file-grid">
                    <?php
                    //Affiche les fichiers uploadés depuis le livret de suivi
                    foreach ($file as $f):
                    if (!empty($f)): ?>
                        <div class="file-card">
                            <div class="file-info">
                                <strong><?= htmlspecialchars($f['name']) ?></strong>
                                <p><?= round($f['size'] / 1024, 2) ?> KB</p>
                            </div>
                            <form method="get" action="Documents/Download.php">
                                <input type="hidden" name="file" value="<?= htmlspecialchars($f['path']) ?>">
                                <button type="submit" class="download-button">Télécharger</button>
                            </form>
                            <form method="post" action="" class="delete-form">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="livretFileId" value="<?= $f['id'] ?>">
                                <button type="submit" class="delete-button">Supprimer</button>
                            </form>
                        </div>
                    <?php endif;
                    endforeach;?>
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