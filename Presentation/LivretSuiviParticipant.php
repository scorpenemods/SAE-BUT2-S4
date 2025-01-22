<?php
// get people internship book

require_once '../Model/Database.php';
require_once '../Model/Person.php';

$database = Database::getInstance();

// Запускаем сессию, если она не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Извлекаем пользователя из сессии
$person = unserialize($_SESSION['user']);
$userId = $person->getId();
$userRole = $person->getRole();

$followUpId = 0;
$meetings   = [];
// Если это студент (role=1), пытаемся récupérer son FollowUpBook
if ($userRole === 1) {
    // Берём группу, зная $userId (ID студента)
    $group = $database->getGroupByUserId($userId);
    if ($group && !empty($group['conv_id'])){
        $followUpId = $database->getOrCreateFollowUpBook($group['conv_id']);
        $meetings = $database->getMeetingsByFollowUp($followUpId);

        // Если у студента совсем нет rencontres — выводим сообщение (как в вашем старом коде)
        if (count($meetings) === 0) {
            echo "<div class='participant-container' style='color:red;'>
                    Le livret de suivi n'est pas encore créé pour vous. Aucune rencontre n'est disponible.
                  </div>";
            return;
        }
    } else {
        // Нет conv_id => значит нет livret
        echo "<div class='participant-container' style='color:red;'>
                Aucune convention ou groupe associé. Le livret n'est pas disponible.
              </div>";
        return;
    }
}
?>
<div style="width: 100%;">
    <div>
        <div style="display: flex; justify-content: center;" id="student-details">
            <?php
            // 2) Si GET['user_id'] est fourni => c'est qu'on veut afficher le Livret d'un étudiant
            //    (pour un Prof ou un Maître de stage)
            $userIdChosen = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
            if ($userIdChosen > 0) {
                // Récupération des infos sur l'étudiant
                $studentInfo = $database->getStudentInfo($userIdChosen);     // Doit renvoyer row avec role=1
                $professorInfo = $database->getProfessorInfo($userIdChosen); // Prof du même conv_id
                $mentorInfo = $database->getMentorInfo($userIdChosen);       // Maître du même conv_id

                // On récupère le followUpId
                $group = $database->getGroupByUserId($userIdChosen);
                $followUpId = 0;
                if ($group && !empty($group['conv_id'])) {
                    $followUpId = $database->getOrCreateFollowUpBook($group['conv_id']);
                }

                // Vérifions s'il y a des meetings
                $meetings = [];
                if ($followUpId) {
                    $meetings = $database->getMeetingsByFollowUp($followUpId);
                }

                // Affichage étudiant
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

                // Affichage prof
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

                // Affichage maître de stage
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

                // Transmettre pour LivretSuiviContenu
                $GLOBALS['studentInfo'] = $studentInfo;
                $GLOBALS['professorInfo'] = $professorInfo;
                $GLOBALS['mentorInfo'] = $mentorInfo;
                $GLOBALS['followUpId'] = $followUpId;
                $GLOBALS['meetingsCount'] = is_array($meetings) ? count($meetings) : 0;

            } else {
                // Si user_id=0 => peut-être c'est un prof/maître qui n'a pas encore cliqué sur un étudiant
                if ($userRole != 1) {
                    echo "<div class='participant-container'>Sélectionnez un étudiant pour voir les détails.</div>";
                } else {
                    // Étudiant, mais pas de GET user_id => «pas de livret» ?
                    echo "<div class='participant-container'>Vous n'avez pas de livret de suivi ouvert pour le moment.</div>";
                }
            } echo "<script>window.followUpId = ".(int)$followUpId.";</script>";
            ?>
        </div>
        <br>

        <div class="livret-container">
            <?php
            if (!empty($GLOBALS['followUpId'])) {
                include_once "LivretSuiviContenu.php";
            }
            ?>
        </div>
    </div>
</div>