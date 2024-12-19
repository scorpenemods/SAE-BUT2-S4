<?php
require_once '../Model/Database.php'; // Inclure la connexion à la base de données
require_once '../Model/Person.php';

// Récupérer l'instance de la classe Database
$database = Database::getInstance();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div style="width: 100%;">
    <div>
        <!-- Section pour l'affichage des informations des participants (étudiant, professeur, maître de stage) -->
        <div style="display: flex; justify-content: center;" id="student-details">
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
                    echo "<h3>Etudiant :</h3>";
                    echo "<p><strong>Nom :</strong> " . htmlspecialchars($studentInfo['nom']) . "</p>";
                    echo "<p><strong>Prénom :</strong> " . htmlspecialchars($studentInfo['prenom']) . "</p>";
                    echo "<p><strong>Email :</strong> " . htmlspecialchars($studentInfo['email']) . "</p>";
                    echo "<p><strong>Téléphone :</strong> " . htmlspecialchars($studentInfo['telephone']) . "</p>";
                    echo "<p><strong>Formation :</strong> " . htmlspecialchars($studentInfo['activite']) . "</p>";
                    echo "</div>";
                } else {
                    echo "<p>Aucune information trouvée pour l'étudiant.</p>";
                }

                // Vérifier si des informations ont été trouvées pour le professeur
                if (!empty($professorInfo) && !isset($professorInfo['error'])) {
                    echo "<div class='participant-info professor-info'>";
                    echo "<h3>Professeur tuteur :</h3>";
                    echo "<p><strong>Nom :</strong> " . htmlspecialchars($professorInfo['nom']) . "</p>";
                    echo "<p><strong>Prénom :</strong> " . htmlspecialchars($professorInfo['prenom']) . "</p>";
                    echo "<p><strong>Email :</strong> " . htmlspecialchars($professorInfo['email']) . "</p>";
                    echo "<p><strong>Téléphone :</strong> " . htmlspecialchars($professorInfo['telephone']) . "</p>";
                    echo "<p><strong>Spécialité :</strong> " . htmlspecialchars($professorInfo['activite']) . "</p>";
                    echo "</div>";
                } else {
                    echo "<p>Aucune information sur le professeur n'a été trouvée.</p>";
                }

                // Vérifier si des informations ont été trouvées pour le maître de stage
                if (!empty($mentorInfo) && !isset($mentorInfo['error'])) {
                    echo "<div class='participant-info mentor-info'>";
                    echo "<h3>Maître de stage :</h3>";
                    echo "<p><strong>Nom :</strong> " . htmlspecialchars($mentorInfo['nom']) . "</p>";
                    echo "<p><strong>Prénom :</strong> " . htmlspecialchars($mentorInfo['prenom']) . "</p>";
                    echo "<p><strong>Email :</strong> " . htmlspecialchars($mentorInfo['email']) . "</p>";
                    echo "<p><strong>Téléphone :</strong> " . htmlspecialchars($mentorInfo['telephone']) . "</p>";
                    echo "<p><strong>Activité professionnelle :</strong> " . htmlspecialchars($mentorInfo['activite']) . "</p>";
                    echo "</div>";
                } else {
                    echo "<div class='participant-container'>Aucune information sur le maître de stage n'a été trouvée.</div>";
                }
                ?>
        </div><br>

        <div class="livret-container">
            <!-- Création des différentes rencontres / dépôts : -->
            <?php include_once "LivretSuiviContenu.php"; ?>
            <script src="../View/Documents/Documents.js"></script>
            <?php

            } else {
                echo "<div class='participant-container'>Sélectionnez un étudiant pour voir les détails.</div>";
            }
            ?>
        </div>
    </div>
</div>