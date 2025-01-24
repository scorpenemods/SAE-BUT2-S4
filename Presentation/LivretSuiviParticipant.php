<?php
// get people internship book
/*
 * Ce script gère l'affichage du livret de stage.
 * Il initialise la session, gère les traductions,
 * récupère les informations de l'utilisateur (étudiant, professeur, mentor),
 * et affiche les rencontres associées.
 * Si un suivi est disponible, il inclut le contenu détaillé du livret.
 */

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


//TRADUCTION

// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = "../Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../Locales/fr.php";
}

// Charger les traductions
$translations = include $langFile;

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
            echo "<div class='participant-container' style='color:red;'>";
                    echo $translations['noCreated'];
            echo "</div>";
            return;
        }
    } else {
        // Нет conv_id => значит нет livret
        echo "<div class='participant-container' style='color:red;'>";
            echo $translations['noConv'];
        echo "</div>";
        return;
    }
}

?>
<link rel="stylesheet" href="../View/Livretnoah/livretnoah.css">
<div style="width: 100%;">
    <div>
        <div style="display: flex; justify-content: center;" id="student-details">
            <?php
            // 2) Si GET['user_id'] est fourni => c'est qu'on veut afficher le Livret d'un étudiant
            //    (pour un Prof ou un Maître de stage)

            $userIdFromGet = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

            // Si la session n'existe pas encore, on l'initialise avec la valeur du GET (ou 0 si non défini)
            if (!isset($_SESSION['register_suivie_livret'])) {
                $_SESSION['register_suivie_livret'] = $userIdFromGet;
            } else {
                // Si un user_id est fourni via GET, on met à jour la session
                if ($userIdFromGet > 0) {
                    $_SESSION['register_suivie_livret'] = $userIdFromGet;
                }
            }

            // Récupération de la valeur finale après mise à jour
            $userIdChosen = $_SESSION['register_suivie_livret'];

            if ($userIdChosen == 0 && $person->getrole()==1){
                $userIdChosen= $userId;
            }

            if ($userIdChosen > 0) {
                    // Récupération des infos sur l'étudiant
                    $studentInfo = $database->getStudentInfo($userIdChosen);     // Doit renvoyer row avec role=1
                    $professorInfo = $database->getProfessorInfo($userIdChosen); // Prof du même conv_id
                    $mentorInfo = $database->getMentorInfo($userIdChosen);       // Maître du même conv_id
                    if (!empty($professorInfo) && !isset($professorInfo['error']) && !empty($mentorInfo) && !isset($mentorInfo['error'])) {
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
                        echo "<h3>";
                        echo $translations['student'];
                        echo "</h3>";
                        echo "<p><strong>";
                        echo $translations['lastname'];
                        echo "</strong> " . htmlspecialchars($studentInfo['nom']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['firstname'];
                        echo "</strong> " . htmlspecialchars($studentInfo['prenom']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['mail'];
                        echo "</strong> " . htmlspecialchars($studentInfo['email']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['phone'];
                        echo "</strong> " . htmlspecialchars($studentInfo['telephone']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['form'];
                        echo "</strong> " . htmlspecialchars($studentInfo['activite']) . "</p>";
                        echo "</div>";
                    } else {
                        echo "<p>";
                        echo $translations['noInfo'];
                        echo "</p>";
                    }

                    // Affichage prof
                    if (!empty($professorInfo) && !isset($professorInfo['error'])) {
                        echo "<div class='participant-info professor-info'>";
                        echo "<h3>";
                        echo $translations['prof'];
                        echo "</h3>";
                        echo "<p><strong>";
                        echo $translations['lastname'];
                        echo "</strong> " . htmlspecialchars($professorInfo['nom']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['firstname'];
                        echo "</strong> " . htmlspecialchars($professorInfo['prenom']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['mail'];
                        echo "</strong> " . htmlspecialchars($professorInfo['email']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['phone'];
                        echo "</strong> " . htmlspecialchars($professorInfo['telephone']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['specie'];
                        echo "</strong> " . htmlspecialchars($professorInfo['activite']) . "</p>";
                        echo "</div>";
                    } else {
                        echo "<p>";
                        echo $translations['noProfInfo'];
                        echo "</p>";
                    }

                    // Affichage maître de stage
                    if (!empty($mentorInfo) && !isset($mentorInfo['error'])) {
                        echo "<div class='participant-info mentor-info'>";
                        echo "<h3>";
                        echo $translations['master'];
                        echo "</h3>";
                        echo "<p><strong>";
                        echo $translations['lastname'];
                        echo "</strong> " . htmlspecialchars($mentorInfo['nom']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['firstname'];
                        echo "</strong> " . htmlspecialchars($mentorInfo['prenom']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['mail'];
                        echo "</strong> " . htmlspecialchars($mentorInfo['email']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['phone'];
                        echo "</strong> " . htmlspecialchars($mentorInfo['telephone']) . "</p>";
                        echo "<p><strong>";
                        echo $translations['profActivitie'];
                        echo "</strong> " . htmlspecialchars($mentorInfo['activite']) . "</p>";
                        echo "</div>";
                    } else {
                        echo "<div class='participant-container'>";
                        echo $translations['noMasterInfo'];
                        echo "</div>";
                    }

                    // Transmettre pour LivretSuiviContenu
                    $GLOBALS['studentInfo'] = $studentInfo;
                    $GLOBALS['professorInfo'] = $professorInfo;
                    $GLOBALS['mentorInfo'] = $mentorInfo;
                    $GLOBALS['followUpId'] = $followUpId;
                    $GLOBALS['meetingsCount'] = is_array($meetings) ? count($meetings) : 0;
                }
                else{
                    echo "<div class='participant-container'>";
                    echo $translations['pasdestage'];
                    echo "</div>";
                }
            } else {
                // Étudiant, mais pas de GET user_id => «pas de livret» ?
                echo "<div class='participant-container'>";
                echo $translations['selectStudDetails'];
                echo "</div>";
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