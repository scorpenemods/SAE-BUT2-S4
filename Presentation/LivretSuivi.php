<?php


require_once '../Model/Database.php';
require_once '../Model/Person.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<body>
<!-- Affichage des participants -->

<div class="livret-header" style="margin-bottom: 10px">
    <h2 style="text-align: center">Participants</h2>
</div>

<?php include_once ("LivretSuiviParticipant.php")?>


</body>