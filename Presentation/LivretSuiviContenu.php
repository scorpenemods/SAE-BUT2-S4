<?php

require_once '../Model/Database.php';
require_once '../Model/Person.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$person = unserialize($_SESSION['user']);
$userRole = $person->getRole();
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
                    <input type="radio" id="Autre" name="Lieu"><label> Autre</label> <input type="text">
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
            <table>
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
                    <td>Avancé</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Ponctualité</td>
                    <td>Intermédiaire</td>
                    <td>Certains retards ne sont pas justifié.</td>
                </tr>
                <tr>
                    <td>Motivation pour le travail</td>
                    <td>Avancé</td>
                    <td>Avance vite dans son travail mais a tendance à s'éparpiller</td>
                </tr>
                <tr>
                    <td>Initiatives personnelles</td>
                    <td>Confirmé</td>
                    <td>Participation active aux réunions et projets</td>
                </tr>
                <tr>
                    <td>Qualité du travail</td>
                    <td>Expert</td>
                    <td>Autonomie et rigueur dans le travail</td>
                </tr>
                <tr>
                    <td>Intérêt pour la découverte de l'entreprise</td>
                    <td>Débutant</td>
                    <td>Manque d'engagement dans l'entreprise</td>
                </tr>
                </tbody>
            </table><br>



            <?php
            $files = $database->getFiles($userId);
            if ($userRole == 1){
                ?>

                <h3 style="margin-bottom: 10px">Veuillez déposer votre rapport de stage ci-dessous :</h3>

                <form class="box" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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

                </div>
            </div>
        </div>
    </div>
</div>