<?php

// Démarre la session au début du script
session_start();

// Inclure les fichiers nécessaires pour les classes Database et Person
require_once "../Model/Database.php";
require_once "../Model/Person.php";

// Initialiser le nom d'utilisateur comme 'Guest' au cas où aucun utilisateur n'est connecté
$userName = "Guest";
// Définir le fuseau horaire sur Paris
date_default_timezone_set('Europe/Paris');

// Vérifie que l'utilisateur est connecté
if (isset($_SESSION['user'])) {
    $person = unserialize($_SESSION['user']);
    if ($person instanceof Person) {
        $userName = htmlspecialchars($person->getPrenom()) . ' ' . htmlspecialchars($person->getNom());
        $senderId = $person->getUserId(); // Récupère l'ID de l'utilisateur
    }
} else {
    header("Location: Logout.php");
    exit();
}

// Instanciation de l'objet Database
$database = new Database();

// Récupération des préférences de l'utilisateur
$preferences = $database->getUserPreferences($senderId);
$darkmode = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? 'checked' : ''; // Gestion du mode sombre

// Récupération des messages entre l'utilisateur actuel et le destinataire
$receiverId = 2; // À définir dynamiquement
$messages = $database->getMessages($senderId, $receiverId);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="/View/Principal/Principal.css">
    <script src="/View/Principal/Principal.js" defer></script>
    <script src="/View/Principal/deleteMessage.js" defer></script>

    <style>
        /* Mode sombre dynamiquement */
        body.dark-mode {
            background-color: #121212;
            color: white;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Appliquer le mode sombre si activé dans les préférences
            let darkModeEnabled = "<?php echo $darkmode; ?>" === 'checked';
            if (darkModeEnabled) {
                document.body.classList.add('dark-mode');
                document.getElementById('theme-switch').checked = true; // Coche le switch pour le mode sombre
            }

            // Gestion du toggle du mode sombre
            document.getElementById('theme-switch').addEventListener('change', function () {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                } else {
                    document.body.classList.remove('dark-mode');
                }
            });
        });
    </script>

</head>
<body>
<header class="navbar">
    <div class="navbar-left">
        <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        <span class="app-name">Le Petit Stage</span>
    </div>
    <div class="navbar-right">
        <button class="mainbtn">
            <p><?php echo $userName; ?></p>
        </button>
        <!-- Language Switch -->
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                <span class="switch-sticker">🇫🇷</span>
                <span class="switch-sticker switch-sticker-right">🇬🇧</span>
            </span>
        </label>

        <button class="mainbtn" onclick="toggleMenu()">
            <img src="../Resources/Param.png" alt="Settings">
        </button>

        <div class="hide-list" id="settingsMenu">
            <a href="Settings.php">Information</a>
            <a href="Logout.php">Deconnexion</a>
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
        <!-- Accueil Content -->
        <div class="Visible" id="content-0">
            <h2>Bienvenue à Le Petit Stage!</h2><br>
            <p>
                Cette application est conçue pour faciliter la gestion des stages pour les étudiants de l'UPHF, les enseignants, les tuteurs et le secrétariat.
            </p><br>
            <ul>
                <li><strong>Messagerie:</strong> Communiquez facilement avec votre tuteur, enseignant, ou autres contacts.</li><br>
                <li><strong>Offres de stage:</strong> Consultez les offres de stage disponibles et postulez directement.</li><br>
                <li><strong>Documents:</strong> Téléchargez et partagez des documents nécessaires pour votre stage.</li><br>
                <li><strong>Livret de suivi:</strong> Suivez votre progression et recevez des retours de votre tuteur ou enseignant.</li><br>
            </ul><br>
        </div>

        <!-- Messagerie Content -->
        <div class="Contenu" id="content-1">
            <!-- Votre section Messagerie ici -->
        </div>

        <!-- Offres Content -->
        <div class="Contenu" id="content-2">Contenu Offres</div>

        <!-- Documents Content -->
        <div class="Contenu" id="content-3">Contenu Documents</div>

        <!-- Livret de suivi Content -->
        <div class="Contenu" id="content-4">Contenu Livret de suivi</div>
    </div>
</section>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo UPHF" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">À propos</a>
</footer>

</body>
</html>
