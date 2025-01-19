<?php
$database = (Database::getInstance());
?>

<div id="preAgreementToValidateModal" class="modal" style="display: none">
    <div class="modal-content-student">
        <span class="close-button">&times;</span>
        <h2>Recherche de pré-conventions à valider</h2>
        <input type="text" id="searchBarToValidate" placeholder="Rechercher un élève...">
        <ul id="studentListToValidate">
            <?php
            $students = $database->getStudentsWithPreAgreementFormInvalid();
            foreach ($students as $student) {
                echo '<li>';
                echo '<a href="PreAgreementFormStudent.php?id=' . htmlspecialchars($student['id']) . '">'. htmlspecialchars($student['nom']) . ' ' . htmlspecialchars($student['prenom']) . '</a>';
                echo ' <button class="link-professor-btn" data-student-id="' . htmlspecialchars($student['id']) . '">Lier un professeur</button>';
                echo ' <button class="link-mentor-btn" data-student-id="' . htmlspecialchars($student['id']) . '">Lier un maître de stage</button>';
                echo '</li>';
            }
            ?>
        </ul>
    </div>
</div>
