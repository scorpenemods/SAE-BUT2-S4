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
    <h1>À propos</h1>

    <p>
        Bienvenue sur <strong><?= $translations['titre_appli'] ?? 'LPS' ?></strong>, une plateforme développée dans le cadre du BUT Informatique à l’IUT de Valenciennes.
    </p>

    <h2>Notre objectif</h2>
    <p>
        Cette plateforme vise à simplifier la gestion des stages entre étudiants, entreprises, enseignants et secrétariat pédagogique. Elle centralise les candidatures, conventions, livrets de suivi, documents et communications dans un outil unique, moderne et sécurisé.
    </p>

    <h2>Équipe de développement</h2>
    <p>
        Ce projet est développé par un groupe d’étudiants passionnés de BUT2 Informatique, parcours Cybersécurité, avec le soutien des enseignants de l’IUT.
    </p>
    <ul>
        <li>Développement web full-stack (PHP, MySQL, JS, CSS)</li>
        <li>Gestion de projet Agile (Sprints, changelogs, diagrammes)</li>
        <li>Déploiement via Docker, Git, PhpStorm</li>
    </ul>

    <h2>Contact</h2>
    <p>
        Pour toute question ou suggestion, vous pouvez nous contacter par email :<br>
        <a href="mailto:secretariat.lps.official@gmail.com">secretariat.lps.official@gmail.com</a>
    </p>

    <h2>Remerciements</h2>
    <p>
        Merci aux enseignants, tuteurs et encadrants pour leur accompagnement tout au long du projet, ainsi qu’à tous les utilisateurs qui participent à améliorer cette plateforme.
    </p>
</main>

<?php include '../View/Footer.php'; ?>
</body>
</html>
