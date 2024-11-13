<?php
$etudiant="Lucie <br> 
Email : lucie@gmail.com <br>
Telephone : 0657284298 <br>
Description : fezrgzgfiqfhuHTY";

$professeur="Julien <br> 
Email : Julien@gmail.com <br>
Telephone : 0657284298 <br>
Description : fezrgzgfiqfhuHTY";

$MDS="Marie <br> 
Email : Marie@gmail.com <br>
Telephone : 0657284298 <br>
Description : fezrgzgfiqfhuHTY";
?>


<body>

<!-- Création des différentes rencontres / dépôts : -->

<div class="livret-container">
    <aside class="livretbar">
        <span class="vignette" onclick="showContent(0)">1ère rencontre</span><br>
        <span class="vignette" onclick="showContent(1)">2ème rencontre</span><br>
        <span class="vignette" onclick="showContent(2)">3ème rencontre</span><br>
        <span class="vignette" onclick="showContent(3)">Dépôt du mémoire ou rapport</span><br>
    </aside>


    <!-- Affichage des informations des participants : -->

    <div class="content-livret">

        <div class="livret-header">
            <h2>Participants </h2>
        </div>
        <div style="display: flex; gap: 200px;">
            <div class="participants">
                <h3>Etudiant :</h3>
                <p><?php echo $etudiant; ?></p>
            </div>

            <div class="participants">
                <h3>Professeur :</h3>
                <p><?php echo $professeur; ?></p>
            </div>

            <div class="participants">
                <h3>Maitre de stage :</h3>
                <p><?php echo $MDS; ?></p>
            </div>
        </div>


        <!-- Les différents formulaire pour chaque rencontre : -->

        <div class="content-section" id="0">
            <h3 style="padding: 10px">Formulaire</h3>
            <div class="livret-header">
                <h3>1ère rencontre</h3>
            </div>

            <p class="participants">Content for the first meeting.</p>
        </div>

        <div class="content-section" id="1">
            <h3 style="padding: 10px">Formulaire</h3>
            <div class="livret-header">
                <h3>2ème rencontre</h3>
            </div>

            <p class="participants">Content for the second meeting.</p>
        </div>

        <div class="content-section" id="2">
            <h3 style="padding: 10px">Formulaire</h3>
            <div class="livret-header">
                <h3>3ème rencontre</h3>
            </div>

            <p class="participants">Content for the third meeting.</p>
        </div>

        <div class="content-section" id="3">
            <div class="livret-header">
                <h3>Dépôt du mémoire ou rapport</h3>
            </div>

            <p class="participants">Content for the report submission.</p>
        </div>
    </div>


</div>



</body>
