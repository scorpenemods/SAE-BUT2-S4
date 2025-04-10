<?php
// LANGAGE NOAH
require_once '../Model/Config.php';

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} else {
    $lang = $_SESSION['lang'] ?? 'fr';
}

$langFile = "../Locales/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "../Locales/fr.php";
}

$translations = include $langFile;
$darkModeEnabled = $_COOKIE['darkMode'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Lien vers la feuille de style par défaut -->
    <link rel="stylesheet" href="../View/AccountCreation/AccountCreation.css">
    <script src="../View/Home/Lobby.js" defer></script>
    <title>Conditions Générales d’Utilisation</title>
    <link rel="stylesheet" href="../View/MentionLegal/confidentialite.css">

</head>
<body class="<?= $darkModeEnabled ? 'dark-mode' : '' ?>">

<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name"><?= $translations['titre_appli'] ?? 'LPS' ?></span>
    </div>
</header>

<main class="privacy-policy-container">
    <h1>Informations légales</h1>

    <h2>Éditeur du site</h2>
    <p>
        Ce site est un projet universitaire réalisé dans le cadre du BUT Informatique à l'IUT de Valenciennes. Il n’a pas vocation commerciale.
    </p>

    <h2>Responsables du projet</h2>
    <p>
        Groupe d’étudiants en BUT2 Informatique - Parcours Cybersécurité.<br>
        Sous la supervision pédagogique de l’équipe enseignante de l’IUT.
    </p>

    <h2>Hébergement</h2>
    <p>
        Le site est hébergé localement ou sur des serveurs pédagogiques de l’établissement à des fins de démonstration et d’évaluation.
    </p>

    <h2>Droits d’auteur</h2>
    <p>
        Tous les contenus présents sur ce site (textes, visuels, code source) sont protégés par le droit d’auteur. Toute reproduction partielle ou totale est interdite sans accord explicite.
    </p>

    <h2>Contact</h2>
    <p>
        Pour toute question, vous pouvez contacter l’équipe projet à l’adresse suivante :<br>
        <a href="mailto:secretariat.lps.official@gmail.com">secretariat.lps.official@gmail.com</a>
    </p>

    <h2>Limitation de responsabilité</h2>
    <p>
        Ce site étant un projet pédagogique, les informations fournies peuvent contenir des erreurs ou approximations. L’équipe ne saurait être tenue responsable d’un usage incorrect des données affichées.
    </p>
</main>

<?php include '../View/Footer.php'; ?>
</body>
</html>
