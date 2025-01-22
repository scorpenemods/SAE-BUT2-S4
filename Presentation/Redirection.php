<?php
// manage redirections
// Vérifier si une langue est définie dans l'URL, sinon utiliser la session ou le français par défaut
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Enregistrer la langue en session
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr'; // Langue par défaut
}

// Vérification si le fichier de langue existe, sinon charger le français par défaut
$langFile = "../locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../locales/fr.php";
}

// Charger les traductions
$translations = include $langFile;


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translations['redirection_title'] ?></title>
    <link rel="stylesheet" href="../View/Redirection/Redirection.css"> <!-- Lien vers le fichier CSS -->
</head>
<body>
<div class="container">
    <h1><?= $translations['redirection'] ?></h1>
    <p><?= $translations['return_phrase'] ?></p>
</div>
<?php
session_start();
$role = $_SESSION['personne']->getRole();
if($role == 4 || $role == 5){
    header('Location: Secretariat.php?section=0');
    die();
}
if($role == 3 ){
    header('Location: MaitreStage.php?section=1');

}
if($role == 2 ){
    header('Location: Professor.php?section=1');

}
if($role == 1){
    header('Location: Student.php?section=1');

}



?>
</body>
</html>
