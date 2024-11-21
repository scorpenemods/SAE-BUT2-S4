<?php

session_start(); // Assurez-vous que la session est démarrée
require_once "../Model/Person.php"; // Ensure Person class is correctly included

// Vérifie si l'utilisateur est connecté en consultant la variable 'user' dans $_SESSION
if (!isset($_SESSION['user'])) {
    header("Location: Logout.php"); // Redirige vers la page de déconnexion si l'utilisateur n'est pas connecté
    session_destroy(); // Détruit toutes les données associées à la session en cours
    exit(); // Termine l'exécution du script
}

$person = unserialize($_SESSION['user']);
$userName = $person->getPrenom() . ' ' . $person->getNom();
$userRole = $person->getRole();

// Détermine la page d'accueil en fonction du rôle de l'utilisateur
$homePage = '';
if ($userRole == 1) {
    $homePage = 'Student.php';
} elseif ($userRole == 2) {
    $homePage = 'Professor.php';
} elseif ($userRole == 3) {
    $homePage = 'MaitreStage.php';
} elseif ($userRole == 4 or $userRole == 5) {
    $homePage = 'Secretariat.php';
}

// Définissez une section par défaut si aucune section n'est sélectionnée
$activeSection = isset($_GET['section']) ? $_GET['section'] : 'account-info';

// Liste des sections autorisées pour éviter les erreurs
$allowedSections = ['account-info', 'preferences'];
if (!in_array($activeSection, $allowedSections)) {
    $activeSection = 'account-info'; // Si la section n'est pas valide, retour à 'account-info'
}

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
<body>
<header class="navbar">
    <div class="navbar-left">
        <a href="<?php echo $homePage; ?>">
            <img src="../Resources/LPS%201.0.png" alt="Logo" class="logo"/>
        </a>
        <span class="app-name">Le Petit Stage</span>
    </div>

    <div class="navbar-right">
        <p><?php echo $userName; ?></p>
        <!-- Language Switch -->
        <label class="switch">
            <input type="checkbox" id="language-switch" onchange="toggleLanguage()">
            <span class="slider round">
                    <span class="switch-sticker">🇫🇷</span>
                    <span class="switch-sticker switch-sticker-right">🇬🇧</span>
                </span>
        </label>
    </div>
</header>

<div class="container">
    <div class="vertical-menu">
        <div class="menu-item" onclick="window.location.href='Settings.php?section=account-info'">
            <span>Information du compte</span>
            <span class="arrow">&#9660;</span>
        </div>
        <div class="menu-item" onclick="window.location.href='Settings.php?section=preferences'">
            <span>Modifier ses préférences</span>
            <span class="arrow">&#9660;</span>
        </div>
        <div class="menu-item" onclick="window.location.href='Logout.php'">
            <span>Déconnexion</span>
            <span class="arrow">&#9660;</span>
        </div>
    </div>
    <div id="main-content" class="main-content">
        <?php
        if ($activeSection == 'account-info') {
            include './Information.php';
        } elseif ($activeSection == 'preferences') {
            include './Preference.php';
        }

        ?>
        <a href='<?php echo $homePage; ?>' style="margin-left: 45%">
            Retour à la page d'accueil
        </a>
    </div>
</div>
</body>

<footer class="PiedDePage">
    <img src="../Resources/Logo_UPHF.png" alt="Logo uphf" width="10%">
    <a href="Redirection.php">Informations</a>
    <a href="Redirection.php">A propos</a>
</footer>
</html>
