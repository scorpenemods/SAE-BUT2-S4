<?php

require_once '../Model/Database.php';
require_once '../Model/Person.php';

$database = Database::getInstance();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$person = unserialize($_SESSION['user']);
$userRole = $person->getRole();

if (empty($_SESSION['livret_token'])) {
    $_SESSION['livret_token'] = bin2hex(random_bytes(32));
}

$groupId = $database->getGroup($studentInfo['id'], $professorInfo['id'], $mentorInfo['id']);

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['livret_token']) && $_POST['livret_token'] === $_SESSION['livret_token']) {
    if (!empty($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            $name = $_FILES['files']['name'][$index];
            $size = $_FILES['files']['size'][$index];
            $error = $_FILES['files']['error'][$index];

            if ($error === UPLOAD_ERR_OK) {
                // Vérifiez l'extension du fichier
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION)); // Récupère l'extension
                if (!in_array($extension, $allowedExtensions)) {
                    echo "Le fichier $name n'est pas autorisé. Seules les images et les PDF sont acceptés.<br>";
                    continue;
                }

                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filePath = $uploadDir . uniqid() . '-' . basename($name);

                if ($database->fileExists($name, $studentInfo['id'])) {
                    continue; // Ignore le fichier si déjà existant
                }
                if (move_uploaded_file($tmpName, $filePath)) {
                    // Ajouter le fichier dans la base de données
                    $database->addLivretFile($name, $filePath, $studentInfo['id'], $size, $groupId);
                }
            }
        }
    }

    // Gérer la suppression des fichiers
    if (!empty($_POST['fileId'])) {
        $database->deleteFile((int)$_POST['fileId']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
}

$file = $database->getLivretFile($groupId);
?>
<!-- Changer le style pour que les formulaires s'affichent à côté des rencontres  -->
<aside class="livretbar">
    <h3 style="text-decoration: underline;">Rencontres / dépôts</h3><br>
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
            </form>



            <?php

            echo '<pre>' . print_r($groupId, true) . '</pre>';
            echo '<pre>' . print_r($file, true) . '</pre>';

            if ($userRole == $userRole){
                ?>

                <h3 style="margin-bottom: 10px">Veuillez déposer votre rapport de stage ci-dessous :</h3>

                <form class="box" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="livret_token" value="<?= $_SESSION['livret_token'] ?>">
                    <div class="box__input">
                        <input type="file" name="files[]" id="file" multiple>
                        <button class="box__button" type="submit">Uploader</button>
                    </div>
                    <div class="box__uploading">Envoi en cours...</div>
                    <div class="box__success">Upload terminé !</div>
                    <div class="box__error">Erreur : <span></span></div>
                </form>
                <?php
            }   ?>

            <div class="file-list">
                <h2>Fichier(s) déposé(s) :</h2>
                <div class="file-grid">

                    <?php foreach ($file as $f):
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
                                <input type="hidden" name="livret_token" value="<?= $_SESSION['livret_token'] ?>">
                                <input type="hidden" name="fileId" value="<?= $f['id'] ?>">
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