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
$darkmode = isset($preferences['darkmode']) && $preferences['darkmode'] == 1 ? 'checked' : '';
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Appliquer le mode sombre si activé dans les préférences
        let darkModeEnabled = "<?php echo $darkmode; ?>" === 'checked';
        if (darkModeEnabled) {
            document.body.classList.add('dark-mode');
        }
    });

</script>
<body class="dark-mode">
<?php include_once("../View/Header.php");?>

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
    </div>
</div>
<?php include "../View/Footer.php" ?>
</body>
</html>
