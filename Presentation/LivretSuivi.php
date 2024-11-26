<?php

session_start();

require_once '../Model/Database.php';
require_once '../Model/Person.php';

$etu = unserialize($_SESSION['user']);
$nameetu = $_SESSION['user_name'];


$db = (Database::getInstance());

$id = $etu->getUserId() ;





?>

<body>


<!-- Affichage des participants -->

<div class="livret-header" style="margin-bottom: 10px">
    <h2 style="text-align: center">Participants</h2>
</div><br>
<div style="display: flex; gap: 10%; justify-content: center;">
    <div class="participants">
        <h3>Etudiant :</h3><br>
        <p>Nom prénom : <label><?php echo $nameetu,$id; ?></label></p>
        <p>Formation : <label><?php echo $etu->getActivite(); ?></label></p>
        <p>Email : <label><?php echo $etu->getEmail(); ?></label></p>
        <?php if ($etu->getTelephone() != null){?>
            <p>Téléphone : <?php echo $etu->getTelephone(); ?></label></p>
        <?php }?>
    </div>

    <div class="participants">
        <h3>Professeur :</h3><br>
        <p>Nom prénom : <label><?php echo '(Professeur)'; ?></label></p>
        <p>Spécialité : <label><?php echo '(Spécialité)' ?></label></p>
        <p>Email : <label><?php echo '(professeur@email.com)' ?></label></p>
        <?php if (0==0){?>
            <p>Téléphone : <label><?php echo '(téléphone)' ?></label></p>
        <?php }?>
    </div>

    <div class="participants">
        <h3>Maitre de stage :</h3><br>
        <p>Nom prénom : <label><?php echo '(MdS)'; ?></label></p>
        <p>Spécialité : <label><?php echo '(Spécialité)' ?></label></p>
        <p>Email : <label><?php echo '(mds@email.com)' ?></label></p>
        <?php if (0==0){?>
            <p>Téléphone : <label><?php echo '(téléphone)' ?></label></p>
        <?php }?>
    </div>
</div><br>


<!-- Création des différentes rencontres / dépôts : -->

<div class="livret-container">
    <aside class="livretbar">
        <h3 style="text-decoration: underline;">Rencontres / dépôts</h3><br>
        <span class="vignette" onclick="showContent(0)">1ère rencontre</span><br>
        <span class="vignette" onclick="showContent(1)">2ème rencontre</span><br>
        <span class="vignette" onclick="showContent(2)">3ème rencontre</span><br>
        <span class="vignette" onclick="showContent(3)">Dépôt du mémoire ou rapport</span><br>
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
            <p class="participants">Date de rencontre : <label style="color: red">*</label> <br>

                <input type="date" name="meeting"/><br><br><br>


                Lieu de la rencontre : <label style="color: red">*</label> <br>

                <input type="radio"><label> En entreprise</label> <br>
                <input type="radio"><label> Par téléphone</label> <br>
                <input type="radio"><label> En visio</label> <br>
                <input type="radio"><label> Autre</label> <input type="text"> <br><br><br>


                Remarques du professeur : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques lors de la rencontre..." class="textareaLivret"></textarea><br><br><br>


                Apréciations du maitre de stage : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques sur l'étudiant en entreprise..." class="textareaLivret"></textarea><br><br><br>

                Remarques de l'étudiant : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques durant votre stage..." class="textareaLivret"></textarea><br><br></p>

            <!-- Validation du formulaire -->
            <div class="validation">
                <h3 style="padding: 10px">Validation du formulaire</h3>
            </div>

                <button>Valider modifications</button>
        </div>

        <!-- Rencontre 2 -->
        <div class="content-section" id="1">
            <h3 style="padding: 10px">Formulaire</h3>
            <div class="livret-header">
                <h3>2ème rencontre</h3>
            </div>
            <!-- Formulaire -->
            <p class="participants">Date de rencontre : <label style="color: red">*</label> <br>

                <input type="date" name="meeting"/><br><br><br>


                Lieu de la rencontre : <label style="color: red">*</label> <br>

                <input type="radio"><label> En entreprise</label> <br>
                <input type="radio"><label> Par téléphone</label> <br>
                <input type="radio"><label> En visio</label> <br>
                <input type="radio"><label> Autre</label> <input type="text"> <br><br><br>


                Remarques du professeur : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques lors de la rencontre..." class="textareaLivret"></textarea><br><br><br>


                Apréciations du maitre de stage : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques sur l'étudiant en entreprise..." class="textareaLivret"></textarea><br><br><br>

                Remarques de l'étudiant : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques durant votre stage..." class="textareaLivret"></textarea><br><br></p>

            <!-- Validation du formulaire -->
            <div class="validation">
                <h3 style="padding: 10px">Validation du formulaire</h3>
            </div>

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


                Apréciations du maitre de stage : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques sur l'étudiant en entreprise..." class="textareaLivret"></textarea><br><br><br>

                Remarques de l'étudiant : <label style="color: red">*</label> <br>

                <textarea name="remarque[]" placeholder="Veuillez entrer vos remarques durant votre stage..." class="textareaLivret"></textarea><br><br></p>


            <!-- Validation du formulaire -->
            <div class="validation">
                <h3 style="padding: 10px">Validation du formulaire</h3>
            </div>

                <button>Valider modifications</button>
        </div>

        <!-- Bilan -->
        <div class="content-section" id="3">
            <div class="livret-header">
                <h3>Bilan du stage</h3>
            </div>

            <p class="participants"> à remplir </p>
        </div>
    </div>


</div>



</body>
