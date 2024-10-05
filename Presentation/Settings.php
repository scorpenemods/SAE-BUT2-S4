<?php

require_once "../Model/Person.php"; // Ensure Person class is correctly included
session_start(); // Démarre une nouvelle session ou reprend une session existante

// Vérifie si l'utilisateur est connecté en consultant la variable 'user' dans $_SESSION
if (!isset($_SESSION['user'])) {
    header("Location: Logout.php"); // Redirige vers la page de déconnexion si l'utilisateur n'est pas connecté
    session_destroy(); // Détruit toutes les données associées à la session en cours
    exit(); // Termine l'exécution du script
}

$person = unserialize($_SESSION['user']);
$userName = $person->getPrenom() . ' ' . $person->getNom();


?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Le Petit Stage</title>
    <link rel="stylesheet" href="../View/Settings/Settings.css">
    <script type="text/javascript" src="../View/Settings/Settings.js"></script>
</head>
    <header class="navbar">
        <div class="navbar-left">
            <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
            <span class="app-name">Le Petit Stage</span>
        </div>

        <div class="navbar-right">
            <p><?php echo $userName; ?></p>
            <!-- Language Switch -->
            <label class="switch">
                <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
                <span class="slider round">
                        <span class="switch-sticker">🇫🇷</span> <!-- Sticker Français -->
                        <span class="switch-sticker switch-sticker-right">🇬🇧</span> <!-- Sticker English -->
                    </span>
            </label>
            <!-- Theme Switch -->
            <label class="switch">
                <input type="checkbox" id="theme-switch" onchange="toggleTheme()">
                <span class="slider round">
                        <span class="switch-sticker switch-sticker-right">🌙</span> <!-- Sticker Dark Mode -->
                        <span class="switch-sticker">☀️</span> <!-- Sticker Light Mode -->
                    </span>
            </label>
            <button class="mainbtn" onclick="turn()"><img src="../Resources/Param.png"></button>
            <div class="hide-list">
                <a href="Preference.php">Preference</a>
                <a href="Logout.php">Deconnexion</a>
            </div>
        </div>
    </header>
<body>
<div class="container">
    <div class="vertical-menu">
        <div class="menu-item" onclick="toggleMenu('account-info', './Information.php')">
            <span>Information du compte</span>
            <span class="arrow">&#9660;</span>
        </div>
        <div class="menu-item" onclick="toggleMenu('preferences', './Preference.php')">
            <span>Modifier ses préférences</span>
            <span class="arrow">&#9660;</span>
        </div>
        <div class="menu-item" onclick="window.location.href='Logout.php'">
            <span>Déconnexion</span>
            <span class="arrow">&#9660;</span>
        </div>
    </div>
    <div id="main-content" class="main-content">

    </div>
</div>
</body>
<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo uphf" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">A propos</a>
</footer>
</html>
