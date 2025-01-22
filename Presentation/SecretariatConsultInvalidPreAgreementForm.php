<?php
// manage invalid pre agreement form for secretariat
$database = (Database::getInstance());
?>

<div id="preAgreementToValidateModal" class="modal" style="display: none">
    <div class="modal-content-student">
        <span class="close-button-m">&times;</span>
        <h2>Recherche de pré-conventions à valider</h2>
        <input type="text" id="searchBarToValidate" placeholder="Rechercher un élève...">
        <ul id="studentListToValidate">
            <?php
            $students = $database->getStudentsWithPreAgreementFormInvalid();
            foreach ($students as $student) {
                echo '<li>';
                echo '<a href="PreAgreementFormStudent.php?id=' . htmlspecialchars($student['id']) . '">'. htmlspecialchars($student['nom']) . ' ' . htmlspecialchars($student['prenom']) . '</a>';
                echo '</li>';
            }
            ?>
        </ul>
    </div>
</div>
