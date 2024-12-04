<?php

require_once '../Model/Database.php';
require_once '../Model/Person.php';

$etu = unserialize($_SESSION['user']);
$nameetu = $_SESSION['user_name'];


$db = (Database::getInstance());

$id = $etu->getId() ;

?>
<body>
<!-- Affichage des participants -->

<div class="livret-header" style="margin-bottom: 10px">
    <h2 style="text-align: center">Participants</h2>
</div>

<?php  include_once ("LivretSuiviParticipant.php")?>

<!-- Création des différentes rencontres / dépôts : -->

<div class="livret-container">
    <aside class="livretbar">
        <h3 style="text-decoration: underline;">Rencontres / dépôts</h3><br>
        <span class="vignette" onclick="showContent(0)">1ère rencontre</span><br>

        <span class="vignette" onclick="showContent(100)">Dépôt du rapport de stage</span><br>

        <button onclick="addMeeting()" type="button">+ Ajouter une rencontre</button>

        <button onclick="deleteMeeting()" type="button">- Supprimer la dernière rencontre</button>
    </aside>

    <!-- Affichage des informations des participants : -->

    <div class="content-livret">

        <!-- Les différents formulaire pour chaque rencontre : -->

        <!-- Rencontre 1 -->
        <div class="content-section" id="0">
            <h3 style="padding: 10px">Formulaires</h3>
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

                    <button onclick="addForm('formContainer-0')" type="button">+ Ajouter un formulaire</button>

                </form>
            </div>
            <div style="display: flex; ">
                <!-- Validation du formulaire -->
                <div class="validation">
                    <h3 style="padding: 10px">Validation du formulaire</h3>

                    <button>Valider modifications</button>
                </div>

        <!-- Rencontre 3 -->
        <div class="content-section" id="2">
            <h3 style="padding: 10px; text-align: left">Formulaire</h3>
            <div class="livret-header">
                <h3>3ème rencontre</h3>
            </div>
            <!-- Formulaire -->
            <p class="participants">Date de rencontre : <label style="color: red">*</label> <br>

                <input type="date" name="meeting"/> <br><br><br>


                Lieu de la rencontre : <label style="color: red">*</label> <br>

                <input type="radio"><label> En entreprise</label> <br>
                <input type="radio"><label> Par téléphone</label> <br>
                <input type="radio"><label> En visio</label> <br>
                <input type="radio"><label> Autre</label> <input type="text"> <br><br><br>


                Remarques du professeur : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques lors de la rencontre..." class="textareaLivret"></textarea><br><br><br>

            <!-- Validation du formulaire -->
            <div class="validation">
                <h3 style="padding: 10px">Validation du formulaire</h3>
            </div>

                <button>Valider modifications</button>
        </div>

        <!-- Bilan -->
        <div class="content-section" id="100">
            <div class="livret-header">
                <h3>Rapport de stage</h3>
            </div>

            <p class="participants"> à remplir </p>
        </div>
    </div>


</div>
</body>