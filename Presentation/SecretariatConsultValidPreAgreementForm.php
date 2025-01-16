<?php
$database = (Database::getInstance());
?>

<div id="preAgreementModal" class="modal" style="display: none">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Recherche de pré-conventions</h2>

        <!-- Barre de recherche -->
        <input type="text" id="searchBar" placeholder="Rechercher un élève...">

        <!-- Liste des élèves -->
        <ul id="studentList">
            <?php
            $students = $database->getStudentsWithPreAgreementFormValid();
            foreach($students as $student){
                echo '<li><a href="PreAgreementFormStudent.php?id=' . htmlspecialchars($student['id']) . '">'. htmlspecialchars($student['nom']) . ' ' . htmlspecialchars($student['prenom']) . '</a>';
            }
            ?>
        </ul>
    </div>
</div>