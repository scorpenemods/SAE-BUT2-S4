<?php
// manage final pre agreement form for studend
$database = (Database::getInstance());
$senderId = $_SESSION['user_id'];
?>

<div id="preAgreementModal" class="modal" style="display: none">
    <div class="modal-content-student">
        <span class="close-button-m">&times;</span>
        <h2>Recherche de pré-conventions</h2>

        <!-- Barre de recherche -->
        <input type="text" id="searchBar-student" placeholder="Rechercher un formulaire...">

        <!-- liste des pré-conventions -->
        <ul id="studentList">
            <?php
            $idPreAgreementForm = $database->getPreAgreementFormStudent($senderId);
            if (!$idPreAgreementForm == null) {
                foreach ($idPreAgreementForm as $idPreAgreementForms) {
                    echo '<li><a href="PreAgreementFormStudent.php?id=' . htmlspecialchars($idPreAgreementForms['id']) . '">Pré-convention du ' . htmlspecialchars($database->getCreationDatePreAgreement($idPreAgreementForms['id'])) . ' ' . '</a></li>';                }
            }else {
                echo "Vous n'avez pas encore demandé de formulaire de pré-convention";
            }
            ?>
        </ul>
    </div>
</div>