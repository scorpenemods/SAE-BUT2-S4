<?php

$database = (Database::getInstance());
$senderId = $_SESSION['user_id'];
?>

<div id="preAgreementModal" class="modal-student" style="display: none">
    <div class="modal-content-student">
        <span class="close-button">&times;</span>
        <h2>Recherche de pré-conventions</h2>

        <!-- Barre de recherche -->
        <input type="text" id="searchBar-student" placeholder="Rechercher un formulaire...">

        <!-- liste des pré-conventions -->
        <ul id="studentList">
            <?php
            $idPreAgreementForm = $database->getPreAgreementFormProfessor($senderId);
            if (!$idPreAgreementForm == null) {
                foreach ($idPreAgreementForm as $idPreAgreementForms) {
                    echo '<a href="PreAgreementFormStudent.php?id=' . htmlspecialchars($idPreAgreementForms['id']) . '">'. htmlspecialchars($idPreAgreementForms['nom']).' '.htmlspecialchars($idPreAgreementForms['prenom']).' '.htmlspecialchars($idPreAgreementForms['created_at']) . '</a>';
                }
            }else {
                echo "Vous n'avez pas encore de formulaire de pré-convention";
            }
            ?>
        </ul>
    </div>
</div>