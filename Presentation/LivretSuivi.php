<?php
$etudiant='Lucie';
$professeur='Julien';
$MDS='Marie';
?>


<body>
<div class="livret-container">
    <aside class="livretbar">
        <span class="vignette" onclick="showContent(0)">1ère rencontre</span><br>
        <span class="vignette" onclick="showContent(1)">2ème rencontre</span><br>
        <span class="vignette" onclick="showContent(2)">3ème rencontre</span><br>
        <span class="vignette" onclick="showContent(3)">Dépôt du mémoire ou rapport</span><br>
    </aside>

    <div class="content-livret">

        <div class="livret-header">
            <h3>Participants </h3>
        </div>
        <div style="display: flex; gap: 20px;">
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





        <div class="content-section" id="0">
            <h2>1ère rencontre</h2>
        </div>
        <div class="content-section" id="1">
            <h2>2ème rencontre</h2>
            <p>Content for the second meeting.</p>
        </div>
        <div class="content-section" id="2">
            <h2>3ème rencontre</h2>
            <p>Content for the third meeting.</p>
        </div>
        <div class="content-section" id="3">
            <h2>Dépôt du mémoire ou rapport</h2>
            <p>Content for the report submission.</p>
        </div>
    </div>


</div>



</body>
