<?php
// Connecter les fichiers nécessaires
/*
 * Ce script affiche les informations d'un étudiant.
 * Il gère les traductions, récupère les données de l'étudiant et de son groupe,
 * et affiche les détails ou des messages d'erreur si nécessaire.
 */

require_once '../Model/Database.php';
$database = Database::getInstance();

// --- [ LOGIQUE DES TRADUCTIONS / MULTI-LANG ] ---
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
}

$langFile = "../Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../Locales/fr.php";
}

// Chargement de la traduction
$translations = include $langFile;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management</title>
</head>
<body>

<section>
    <div id="student-infos">
        <?php
        // Vérification si user_id est transmis dans GET
        if (isset($_GET['user_id'])) {
            $userId = intval($_GET['user_id']);

            // information about the student
            $studentInfo = $database->getStudentInfo($userId);

            if (!empty($studentInfo) && !isset($studentInfo['error'])) {

                // Trying to get information about the group
                $convInfo = $database->getGroupByUserId($userId);

                // We check that the returned array is not empty (and there is a group_id key)
                if (!empty($convInfo) && isset($convInfo['conv_id'])) {
                    $convId = $convInfo['conv_id'];
                } else {
                    // if not found
                    $convId = null;
                }

                // Displaying information about a student
                echo "<div class='participant-info student-info'>";
                echo "<h3>" . $translations['uploadedFile'] . "</h3>";
                echo "<p><strong>" . $translations['lastname'] . ":</strong> " . htmlspecialchars($studentInfo['nom']) . "</p>";
                echo "<p><strong>" . $translations['firstname'] . ":</strong> " . htmlspecialchars($studentInfo['prenom']) . "</p>";
                echo "<p><strong>" . $translations['mail'] . ":</strong> " . htmlspecialchars($studentInfo['email']) . "</p>";
                echo "<p><strong>" . $translations['phone'] . ":</strong> " . htmlspecialchars($studentInfo['telephone']) . "</p>";
                echo "<p><strong>" . $translations['activity'] . ":</strong> " . htmlspecialchars($studentInfo['activite']) . "</p>";

                // End internship button (only if $groupId could be retrieved)
                if ($convId) {
                    echo "<button onclick='endStage(" . $convId . ")'>";
                    echo $translations['endStage'];
                    echo "</button>";
                } else {
                    echo "<p style='color: red;'>"
                        . "Impossible de déterminer la groupe de l'étudiant."
                        . "</p>";
                }
                echo "</div>";
            } else {
                echo "<p>" . $translations['noInfo'] . "</p>";
            }
        } else {
            // if not transferred user_id
            echo "<p>" . $translations['selectStudent'] . "</p>";
        }
        ?>
    </div>
</section>

</body>
</html>
