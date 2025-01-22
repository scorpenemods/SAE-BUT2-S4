<?php
// manage internship book

require_once '../Model/Database.php';
require_once '../Model/Person.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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
<body>
<!-- Affichage des participants -->

<div class="livret-header" style="margin-bottom: 10px">
    <h2 style="text-align: center"><?= $translations['participants']?></h2>
</div>

<?php include_once ("LivretSuiviParticipant.php")?>


</body>