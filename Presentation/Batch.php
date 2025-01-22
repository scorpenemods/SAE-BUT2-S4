<?php
// Ajoute des utilisateurs via un fichier CSV
include_once("../Model/CreationBatch.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
    $filePath = $_FILES['csv_file']['tmp_name'];
    importCsv($filePath);
    echo "CSV import completed successfully.";
} else {
    echo "File upload error. Please upload a valid CSV file.";
}

// TRADUCTION

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
<h2><?= $translations['import des utilisateurs terminé'] ?></h2>

<button onclick="window.location.href = 'Secretariat.php';"><?= $translations['cliquer pour retourner à la Gestion des Utilisateurs'] ?></button>

