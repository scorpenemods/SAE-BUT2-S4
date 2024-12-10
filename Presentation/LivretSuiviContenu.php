<?php

require_once '../Model/Database.php';
require_once '../Model/Person.php';
?>
<div> <!-- Changer le style pour que les formulaires s'affichent à côté des rencontres  -->
    <aside class="livretbar">
        <h3 style="text-decoration: underline;">Rencontres / dépôts</h3><br>
        <span class="vignette" onclick="showContent(0)">1ère rencontre</span><br>

        <span class="vignette" onclick="showContent(1)">Dépôt du rapport de stage</span><br>

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
            <div class="livret-header">
                <h3>Bilan</h3>
            </div>

            <div class="participants">
                à remplir
            </div>
        </div>
    </div>
</div>