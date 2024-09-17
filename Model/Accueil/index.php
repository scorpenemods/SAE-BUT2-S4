<?php
session_start();
// A realiser logique de connection et changement de theme

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="lobby.css">
    <script src="accueil.js" defer></script>
</head>
<body>
<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-left">
        <img src="/Model/Accueil/LPS1.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>
    <div class="navbar-right">
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
                <span class="switch-sticker">🌙</span> <!-- Sticker Dark Mode -->
                <span class="switch-sticker switch-sticker-right">☀️</span> <!-- Sticker Light Mode -->
            </span>
        </label>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <h1 class="main-heading">Vous êtes un étudiant en stage à UPHF?<br> Nous avons la solution!</h1>
    <p class="sub-text">
        Une application innovante pour les étudiants, enseignants et personnel de l'UPHF. Gérez vos stages et restez connectés avec toutes les parties prenantes facilement et efficacement.
    </p>

    <div class="button-group">
        <button class="primary-button">Demander la création de compte</button>
        <button class="secondary-button">Se connecter</button>
        <button class="secondary-button">S’enregistrer</button>
    </div>
</div>
</body>
</html>