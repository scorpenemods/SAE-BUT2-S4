<?php
require_once '../Model/Database.php'; // Inclure la connexion à la base de données

// Récupérer l'instance de la classe Database
$database = Database::getInstance();

?>


<div class="participant-container">
    <!-- Section pour l'affichage des informations des participants (étudiant, professeur, maître de stage) -->
    <div  style="display: flex; gap: 10%; justify-content: center;" id="student-details">
        <?php
        if (isset($_GET['user_id'])) {
            $userId = intval($_GET['user_id']); // Assurez-vous que l'ID est un entier

            // Appeler les méthodes pour obtenir les informations nécessaires
            $studentInfo = $database->getStudentInfo($userId);
            $professorInfo = $database->getProfessorInfo($userId);
            $mentorInfo = $database->getMentorInfo($userId);

            // Vérifier si des informations ont été trouvées pour l'étudiant
            if (!empty($studentInfo) && !isset($studentInfo['error'])) {
                echo "<div class='participant-info student-info'>";
                echo "<h3>Informations sur l'étudiant :</h3>";
                echo "<p><strong>Nom :</strong> " . htmlspecialchars($studentInfo['nom']) . "</p>";
                echo "<p><strong>Prénom :</strong> " . htmlspecialchars($studentInfo['prenom']) . "</p>";
                echo "<p><strong>Email :</strong> " . htmlspecialchars($studentInfo['email']) . "</p>";
                echo "<p><strong>Téléphone :</strong> " . htmlspecialchars($studentInfo['telephone']) . "</p>";
                echo "<p><strong>Activité :</strong> " . htmlspecialchars($studentInfo['activite']) . "</p>";
                echo "</div>";
            } else {
                echo "<p>Aucune information trouvée pour l'étudiant.</p>";
            }

            // Vérifier si des informations ont été trouvées pour le professeur
            if (!empty($professorInfo) && !isset($professorInfo['error'])) {
                echo "<div class='participant-info professor-info'>";
                echo "<h3>Informations sur le professeur :</h3>";
                echo "<p><strong>Nom :</strong> " . htmlspecialchars($professorInfo['nom']) . "</p>";
                echo "<p><strong>Prénom :</strong> " . htmlspecialchars($professorInfo['prenom']) . "</p>";
                echo "<p><strong>Email :</strong> " . htmlspecialchars($professorInfo['email']) . "</p>";
                echo "<p><strong>Téléphone :</strong> " . htmlspecialchars($professorInfo['telephone']) . "</p>";
                echo "</div>";
            } else {
                echo "<p>Aucune information sur le professeur n'a été trouvée.</p>";
            }

            // Vérifier si des informations ont été trouvées pour le maître de stage
            if (!empty($mentorInfo) && !isset($mentorInfo['error'])) {
                echo "<div class='participant-info mentor-info'>";
                echo "<h3>Informations sur le maître de stage :</h3>";
                echo "<p><strong>Nom :</strong> " . htmlspecialchars($mentorInfo['nom']) . "</p>";
                echo "<p><strong>Prénom :</strong> " . htmlspecialchars($mentorInfo['prenom']) . "</p>";
                echo "<p><strong>Email :</strong> " . htmlspecialchars($mentorInfo['email']) . "</p>";
                echo "<p><strong>Téléphone :</strong> " . htmlspecialchars($mentorInfo['telephone']) . "</p>";
                echo "</div>";
            } else {
                echo "<p>Aucune information sur le maître de stage n'a été trouvée.</p>";
            }

        } else {
            echo "<p>Sélectionnez un étudiant pour voir les détails.</p>";
        }
        ?>
    </div>
</div><br>