<?php
// manage student
require_once '../Model/Database.php';
$database = Database::getInstance();
?>
<section>
    <div id="student-infos">
        <?php
            if (isset($_GET['user_id'])) {
                $userId = intval($_GET['user_id']); // Assurez-vous que l'ID est un entier

                // Appeler les méthodes pour obtenir les informations nécessaires
                $studentInfo = $database->getStudentInfo($userId);

                $studentId = $studentInfo['id'];

                // Vérifier si des informations ont été trouvées pour l'étudiant
                if (!empty($studentInfo) && !isset($studentInfo['error'])) {
                    echo "<div class='participant-info student-info'>";
                    echo "<h3>Informations sur l'étudiant :</h3>";
                    echo "<p><strong>Nom :</strong> " . htmlspecialchars($studentInfo['nom']) . "</p>";
                    echo "<p><strong>Prénom :</strong> " . htmlspecialchars($studentInfo['prenom']) . "</p>";
                    echo "<p><strong>Email :</strong> " . htmlspecialchars($studentInfo['email']) . "</p>";
                    echo "<p><strong>Téléphone :</strong> " . htmlspecialchars($studentInfo['telephone']) . "</p>";
                    echo "<p><strong>Activité :</strong> " . htmlspecialchars($studentInfo['activite']) . "</p>";
                    echo "<form method='POST' action='Professor.php'>";
                    echo "<button type='submit' name='stage' value=$studentId>Mettre fin au stage</button>";
                    echo "</form>";
                    echo "</div>";
                } else {
                    echo "<p>Aucune information trouvée pour l'étudiant.</p>";
                }
            }
            else {
                echo "<p>Sélectionnez un étudiant pour le gérer.</p>";
            }
        ?>
    </div>
</section>