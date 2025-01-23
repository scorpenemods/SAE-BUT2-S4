<?php
// manage internship quest section
require_once "../Model/Database.php";
// Instanciation de l'objet Database (singleton pour une seule instance de connexion)
$database = (Database::getInstance());
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

function getFieldValue($field, $inputs = null, $default = 'Pas défini'): string
{
    if (isset($inputs[$field])) {
        return htmlspecialchars($inputs[$field]); // Priorité aux valeurs de $inputs
    }
    if ($default !== null) {
        return htmlspecialchars($default); // Sinon, utilise la valeur par défaut
    }
    return ''; // Sinon, champ vide
}?>
<div style="display: flex; justify-content: center;" id="mission-details">
    <?php
    if (isset($_GET['stage_id'])) {
        $userId = intval($_GET['stage_id']);
        $missions = $database->getInputsPreAgreementFormByUserId($userId);
        if ($missions && isset($missions['inputs'])) { // Check if $missions is valid
            $inputs = json_decode($missions['inputs'], true);
            try {
                echo '<br>';
                echo getFieldValue('intershipSubject', $inputs);
                echo '<br>';
                echo getFieldValue('tasksFunctions', $inputs);
            } catch (ErrorException $e) {
                echo $translations['impossible'];
            }
        } else {
            echo $translations['noMission'];
        }
    } else {
        echo $translations['noStudent'];
    }
    ?>

</div>