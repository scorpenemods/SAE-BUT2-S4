<?php
// manage student
require_once '../Model/Database.php';
$database = Database::getInstance();
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
                    echo "<h3>";
                        echo $translations['uploadedFile'];
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
                        echo $translations['activity'];
                        echo "</strong> " . htmlspecialchars($studentInfo['activite']) . "</p>";
                    echo "<form method='POST' action='Professor.php'>";
                    echo "<button type='submit' name='stage' value=$studentId>";
                        echo $translations['endStage'];
                        echo "</button>";
                    echo "</form>";
                    echo "</div>";
                } else {
                    echo "<p>";
                        echo $translations['noInfo'];
                    echo "</p>";
                }
            }
            else {
                echo "<p>";
                    echo $translations['selectStudent'];
                echo "</p>";
            }
        ?>
    </div>
</section>