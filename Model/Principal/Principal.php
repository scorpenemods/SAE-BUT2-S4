<?php
session_start(); // Start the session at the beginning of the script

require_once "../../Class/Database.php"; // Assuming your Personne class is here, or included in Database.php
require_once "../../Class/Personne.php"; // Ensure Personne class is correctly included

// Initialize user name as Guest in case no user is logged in
$userName = "Guest";

// Check if the user is logged in and retrieve their name
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Personne) { // Check if the unserialized object is indeed a Personne object
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom()); // Safely encode output to prevent XSS
    }
} else {
    // If no user is found in session, redirect to the login page
    header("Location: ../Deconnexion/Deconnexion.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="Principal.css">
    <script type="text/javascript" src="./Principal.js"></script>
</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <img src="../../Ressources/LPS 1.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>

    <div class="navbar-right">
        <p><?php echo $userName; ?></p> <!-- Display the dynamically retrieved user name -->
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
        <!-- Settings Button with Dropdown -->
        <button class="mainbtn" onclick="toggleMenu()">
            <img src="../../Ressources/Param.png" alt="Settings">
        </button>
        <div class="hide-list" id="settingsMenu">
            <a href="../Parametre/Information/Information.php">Information</a>
            <a href="../Deconnexion/Deconnexion.php">Deconnexion</a>
        </div>
    </div>
</header>

<section class="Menus">
    <nav>
        <span onclick="widget(0)" class="widget-button Current">Accueil</span>
        <span onclick="widget(1)" class="widget-button">Messagerie</span>
        <span onclick="widget(2)" class="widget-button">Offres</span>
        <span onclick="widget(3)" class="widget-button">Documents</span>
        <span onclick="widget(4)" class="widget-button">Livret de suivi</span>
    </nav>
    <div class="Contenus">
        <div class="Visible" id="content-0">Contenu Accueil</div>

        <!-- Messenger interface -->
        <div class="Contenu" id="content-1">
            <div class="messenger">
                <div class="contacts">
                    <h3>Contacts</h3>
                    <ul>
                        <li>Contact 1</li>
                        <li>Contact 2</li>
                        <li>Contact 3</li>
                    </ul>
                </div>
                <div class="chat-window">
                    <div class="chat-header">
                        <h3>Chat avec Contact 1</h3>
                    </div>
                    <div class="chat-body">
                        <div class="message">Message de Contact 1</div>
                        <div class="message">Votre message</div>
                    </div>
                    <div class="chat-footer">
                        <input type="text" placeholder="Tapez un message...">
                        <button>Envoyer</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="Contenu" id="content-2">Contenu Offres</div>
        <div class="Contenu" id="content-3">Contenu Documents</div>
        <div class="Contenu" id="content-4">Contenu Livret de suivi</div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../../Ressources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="../Redirection/Redirection.php">Informations</a>
    <a href="../Redirection/Redirection.php">À propos</a>
</footer>

<script>
    function toggleMenu() {
        const menu = document.getElementById('settingsMenu');
        menu.classList.toggle('hide-list');
        menu.classList.toggle('show-list');
    }

    function widget(index) {
        const contents = document.querySelectorAll('.Contenus .Contenu');
        const buttons = document.querySelectorAll('.widget-button');

        // Убираем активный класс с кнопок и содержимого
        buttons.forEach(btn => btn.classList.remove('Current'));
        contents.forEach(content => content.classList.remove('Visible'));

        // Добавляем активный класс к выбранному содержимому и кнопке
        buttons[index].classList.add('Current');
        contents[index].classList.add('Visible');
    }
</script>
</body>
</html>